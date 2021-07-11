<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\GroupingRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatch;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatchLight;

class GraphGroups
{

    /**
     * @var array<PatternMatchLight>
     */
    private array $groups = [];

    /**
     * @var GroupingRule[]
     */
    private array $groupingRules;

    /**
     * @var GroupingConditionHandlerInterface[]
     */
    private array $groupingConditionHandlers;

    /**
     * @var array<string, GroupsFilterHandlerInterface>
     */
    private array $groupsFilterHandlers;

    private bool $closed = false;


    /**
     * GraphGroups constructor.
     *
     * @param array<GroupingRule>                             $groupingRules
     * @param array<string,GroupingConditionHandlerInterface> $groupingConditionHandlers
     * @param array<string, GroupsFilterHandlerInterface>     $groupsFilterHandlers
     */
    public function __construct(array $groupingRules, array $groupingConditionHandlers, array $groupsFilterHandlers)
    {
        $this->groupingRules             = $groupingRules;
        $this->groupingConditionHandlers = $groupingConditionHandlers;
        $this->groupsFilterHandlers      = $groupsFilterHandlers;
    }


    public function add(PatternMatch $patternMatch): void
    {
        if ($this->closed) {
            throw new \RuntimeException('GraphGroups is closed. You cannot add pattern match to closed GraphGroups');
        }
        $matchKey = $this->makeMatchKey($patternMatch);

        if ( ! isset($this->groups[$matchKey])) {
            $this->groups[$matchKey] = [];
        }

        $this->groups[$matchKey][] = $patternMatch->toPatternMatchLight();
    }


    private function makeMatchKey(PatternMatch $patternMatch): string
    {
        $keyParts = [];
        foreach ($this->groupingRules as $groupingRule) {
            $nodeAlias = $groupingRule->getNodeAlias();

            $entity = $patternMatch->getEntityByAlias($nodeAlias);

            foreach ($groupingRule->getConditions() as $condition) {
                $conditionHandler = $this->groupingConditionHandlers[get_class($condition)];

                $keyParts[] = $conditionHandler->getMatchKey($condition, $entity);
            }
        }

        return implode('_', $keyParts);
    }


    /**
     * @return array<PatternMatchLight>
     */
    public function getGroups(): array
    {
        if ( ! $this->closed) {
            throw new \RuntimeException('GraphGroups must be closed before accessing groups.');
        }

        return array_values($this->groups);
    }


    private function filterGroups(): void
    {
        foreach ($this->groupingRules as $groupingRule) {
            foreach ($groupingRule->getFilters() as $groupsFilter) {
                $groupsFilterHandler = $this->groupsFilterHandlers[get_class($groupsFilter)];

                $this->groups = $groupsFilterHandler->filter($groupsFilter, $this->groups);
            }
        }
    }


    public function close(): void
    {
        $this->closed = true;
        $this->filterGroups();
    }
}