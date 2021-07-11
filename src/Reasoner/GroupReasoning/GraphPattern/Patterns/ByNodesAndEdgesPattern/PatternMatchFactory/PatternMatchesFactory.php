<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternMatchFactory;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatch;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternGraph;

class PatternMatchesFactory
{

    /**
     * @var EAVEntityRepositoryInterface
     */
    private EAVEntityRepositoryInterface $entityRepository;

    /**
     * @var EAVEntityRelationRepositoryInterface
     */
    private EAVEntityRelationRepositoryInterface $relationRepository;

    private EAVEntityManagerInterface $em;


    public function __construct(EAVEntityRepositoryInterface $entityRepository, EAVEntityRelationRepositoryInterface $relationRepository, EAVEntityManagerInterface $em)
    {
        $this->entityRepository   = $entityRepository;
        $this->relationRepository = $relationRepository;
        $this->em                 = $em;
    }


    /**
     * @param array        $rows
     * @param PatternGraph $patternGraph
     *
     * @return \Generator<PatternMatch>
     */
    public function fromRows(array $rows, PatternGraph $patternGraph): \Generator
    {
        $entityIds = [];

        $nodeColumnToAliasMapping = [];
        foreach ($patternGraph->getNodes() as $node) {
            $nodeColumnToAliasMapping[$node->getIdColumnAlias()] = $node->getNodeSelector()->getAlias();
        }

        foreach ($rows as $row) {
            foreach ($nodeColumnToAliasMapping as $column => $alias) {
                $column      = strtolower($column);
                $entityIds[] = $row[$column];
            }
        }

        $entityIds = array_unique($entityIds);

        $entities = $this->entityRepository->findBy([ (new FilterCriteria())->whereIn('id', $entityIds) ]);

        $entities = array_combine(array_map(static function (EAVEntityInterface $entity) { return $entity->getId(); }, $entities), $entities);

        // remove from uow
        $this->em->forget(...array_values($entities));

        foreach ($rows as $row) {
            $nodeAliasToEntity = [];
            foreach ($nodeColumnToAliasMapping as $column => $alias) {
                $column                    = strtolower($column);
                $nodeAliasToEntity[$alias] = $entities[$row[$column]];
            }

            yield new PatternMatch($nodeAliasToEntity);
        }

        unset($entities);

    }
}