<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node;

interface NodeSelectorInterface
{

    public function getAlias(): string;


    /**
     * @return NodeFilterInterface[]
     */
    public function getNodeFilters(): array;
}