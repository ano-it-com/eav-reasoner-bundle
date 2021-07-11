<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterInterface;

class GroupingRule
{

    private string $nodeAlias;

    /**
     * @var GroupingConditionInterface[]
     */
    private array $conditions;

    /**
     * @var GroupsFilterInterface[]
     */
    private array $filters;


    /**
     * GroupingRule constructor.
     *
     * @param string                            $nodeAlias
     * @param array<GroupingConditionInterface> $conditions
     * @param array<GroupsFilterInterface>      $filters
     */
    public function __construct(string $nodeAlias, array $conditions = [], array $filters = [])
    {
        $this->nodeAlias  = $nodeAlias;
        $this->conditions = $conditions;
        $this->filters    = $filters;
    }


    public function getNodeAlias(): string
    {
        return $this->nodeAlias;
    }


    /**
     * @return GroupingConditionInterface[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }


    /**
     * @return GroupsFilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}