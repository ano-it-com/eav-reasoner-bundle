<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use Doctrine\DBAL\Query\QueryBuilder;

interface NodeFilterHandlerInterface
{

    public static function getSupportedFilter(): string;


    /**
     * @param NodeFilterInterface          $nodeFilter
     * @param QueryBuilder                 $qb
     * @param string                       $nodeTableAlias
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $conditionIndex
     */
    public function handle(NodeFilterInterface $nodeFilter, QueryBuilder $qb, string $nodeTableAlias, array $namespaces, int $conditionIndex): void;
}