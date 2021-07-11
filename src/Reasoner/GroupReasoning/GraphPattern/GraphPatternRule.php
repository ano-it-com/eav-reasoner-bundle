<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\GroupingRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

class GraphPatternRule implements ReasonerRuleInterface
{

    private GraphPatternInterface $pattern;

    private GraphPatternActionInterface $action;

    /**
     * @var array<GroupingRule>
     */
    private array $groupingRules;


    /**
     * @param GraphPatternInterface       $pattern
     * @param array<GroupingRule>         $groupingRules
     * @param GraphPatternActionInterface $action
     */
    public function __construct(
        GraphPatternInterface $pattern,
        array $groupingRules,
        GraphPatternActionInterface $action
    ) {

        $this->pattern       = $pattern;
        $this->groupingRules = $groupingRules;
        $this->action        = $action;
    }


    public function getPattern(): GraphPatternInterface
    {
        return $this->pattern;
    }


    public function getAction(): GraphPatternActionInterface
    {
        return $this->action;
    }


    /**
     * @return GroupingRule[]
     */
    public function getGroupingRules(): array
    {
        return $this->groupingRules;
    }
}