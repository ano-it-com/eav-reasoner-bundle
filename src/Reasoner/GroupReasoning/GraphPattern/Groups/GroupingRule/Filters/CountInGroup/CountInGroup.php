<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\CountInGroup;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterInterface;

class CountInGroup implements GroupsFilterInterface
{

    private string $nodeAlias;

    private string $operator;

    private int $value;


    public function __construct(string $nodeAlias, string $operator, int $value)
    {
        $this->nodeAlias = $nodeAlias;
        $this->operator  = $operator;
        $this->value     = $value;
    }


    public function getNodeAlias(): string
    {
        return $this->nodeAlias;
    }


    public function getOperator(): string
    {
        return $this->operator;
    }


    public function getValue(): int
    {
        return $this->value;
    }
}