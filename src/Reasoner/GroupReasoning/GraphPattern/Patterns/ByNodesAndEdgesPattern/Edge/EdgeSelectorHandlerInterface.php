<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternEdge;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternNode;
use Doctrine\DBAL\Query\QueryBuilder;

interface EdgeSelectorHandlerInterface
{

    public static function getSupportedSelector(): string;


    /**
     * @param PatternEdge                  $edge
     * @param PatternNode                  $node
     * @param QueryBuilder                 $qb
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $conditionIndex
     */
    public function handle(PatternEdge $edge, PatternNode $node, QueryBuilder $qb, array $namespaces, int $conditionIndex): void;
}