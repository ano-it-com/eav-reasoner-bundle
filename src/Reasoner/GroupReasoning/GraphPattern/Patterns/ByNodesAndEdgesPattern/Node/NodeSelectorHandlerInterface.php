<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\NodeProcessContext;
use Doctrine\DBAL\Query\QueryBuilder;

interface NodeSelectorHandlerInterface
{

    public static function getSupportedSelector(): string;


    public function handle(NodeProcessContext $context, QueryBuilder $qb, array $namespaces, int $conditionIndex): void;

}