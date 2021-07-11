<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern;

class NumbersSequence
{

    private int $counter = 0;


    public function getNext(): int
    {
        return $this->counter++;
    }
}