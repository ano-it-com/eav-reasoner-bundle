<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\Selectors;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\NodeProcessContext;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternEdge;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternNode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EdgeByTypeHandler implements EdgeSelectorHandlerInterface
{

    private EAVEntityManagerInterface $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public static function getSupportedSelector(): string
    {
        return EdgeByType::class;
    }


    public function handle(PatternEdge $edge, PatternNode $node, QueryBuilder $qb, array $namespaces, int $conditionIndex): void
    {
        $relationsTable = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY_RELATION);

        $nodeSide           = NodeProcessContext::getNodePositionSide($node, $edge);
        $isNodePositionFrom = $nodeSide === NodeProcessContext::EDGE_FROM;

        $edgeTableColumn = $isNodePositionFrom ? 'from_id' : 'to_id';
        $nodeTableAlias  = $node->getTableAlias();
        $edgeTableAlias  = $this->makeTableAlias($edge, $conditionIndex);

        $qb->leftJoin($nodeTableAlias, $relationsTable, $edgeTableAlias, $nodeTableAlias . '.id = ' . $edgeTableAlias . '.' . $edgeTableColumn);

        /** @var EdgeByType $edgeSelector */
        $edgeSelector = $edge->getEdgeSelector();

        $this->applyTypesCondition($qb, $edgeTableAlias, $edgeSelector->getRelationTypeIds(), $conditionIndex);

        $namespaceIds = array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces);
        $this->applyNamespacesCondition($qb, $edgeTableAlias, $namespaceIds, $conditionIndex);

        $edge->setProcessed($edgeTableAlias);
    }


    private function makeTableAlias(PatternEdge $edge, $index): string
    {
        return 'r_' . $edge->getUniqueKey() . $index;
    }


    /**
     * @param QueryBuilder  $qb
     * @param string        $edgeTableAlias
     * @param array<string> $typeIds
     * @param int           $conditionIndex
     */
    private function applyTypesCondition(QueryBuilder $qb, string $edgeTableAlias, array $typeIds, int $conditionIndex): void
    {
        $typesParamName = 'edgeTypes_' . $conditionIndex;
        $qb->andWhere($edgeTableAlias . '.type_id IN (:' . $typesParamName . ')')->setParameter($typesParamName, $typeIds, Connection::PARAM_STR_ARRAY);
    }


    /**
     * @param QueryBuilder  $qb
     * @param string        $edgeTableAlias
     * @param array<string> $namespaceIds
     * @param int           $conditionIndex
     */
    private function applyNamespacesCondition(QueryBuilder $qb, string $edgeTableAlias, array $namespaceIds, int $conditionIndex): void
    {
        $namespacesParamName = 'edgeNamespaces_' . $conditionIndex;
        $qb->andWhere($edgeTableAlias . '.namespace_id IN (:' . $namespacesParamName . ')')->setParameter($namespacesParamName, $namespaceIds, Connection::PARAM_STR_ARRAY);
    }
}