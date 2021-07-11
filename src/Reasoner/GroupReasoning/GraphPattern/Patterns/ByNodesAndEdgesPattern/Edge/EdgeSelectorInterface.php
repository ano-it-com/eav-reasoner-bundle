<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge;

interface EdgeSelectorInterface
{

    public function getFromNodeAlias(): string;


    public function getToNodeAlias(): string;


    public function getUniqueKey(): string;
}