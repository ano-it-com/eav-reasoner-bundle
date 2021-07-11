<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\Filters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeFilterHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeFilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class NotEmptyEntityHandler implements NodeFilterHandlerInterface
{

    private EAVEntityManagerInterface $em;


    public function __construct(EAVEntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function handle(NodeFilterInterface $nodeFilter, QueryBuilder $qb, string $nodeTableAlias, array $namespaces, int $conditionIndex): void
    {
        /** @var NotEmptyEntity $nodeFilter */

        $valuesTable = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::VALUES);

        $valuesTableAlias = $nodeTableAlias . '_values' . $conditionIndex;

        $qb->leftJoin($nodeTableAlias, $valuesTable, $valuesTableAlias, $nodeTableAlias . '.id = ' . $valuesTableAlias . '.entity_id');

        $qb->andHaving('count(' . $valuesTableAlias . '.id) > 0');
    }


    public static function getSupportedFilter(): string
    {
        return NotEmptyEntity::class;
    }
}