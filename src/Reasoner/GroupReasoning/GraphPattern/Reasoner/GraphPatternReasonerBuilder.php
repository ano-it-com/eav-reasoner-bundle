<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Reasoner;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\GraphPatternRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionsLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFiltersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternMatchersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory\ReasonerBuilderInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

class GraphPatternReasonerBuilder implements ReasonerBuilderInterface
{

    private GraphPatternMatchersLocator $matchersLocator;

    private GraphPatternActionHandlersLocator $actionsLocator;

    private GroupingConditionsLocator $groupingConditionsLocator;

    private GroupsFiltersLocator $groupsFiltersLocator;


    public function __construct(
        GraphPatternMatchersLocator $matchersLocator,
        GraphPatternActionHandlersLocator $actionsLocator,
        GroupingConditionsLocator $groupingConditionsLocator,
        GroupsFiltersLocator $groupsFiltersLocator
    ) {
        $this->matchersLocator           = $matchersLocator;
        $this->actionsLocator            = $actionsLocator;
        $this->groupingConditionsLocator = $groupingConditionsLocator;
        $this->groupsFiltersLocator = $groupsFiltersLocator;
    }


    public static function supports(ReasonerRuleInterface $rule): bool
    {
        return $rule instanceof GraphPatternRule;
    }


    public function build(ReasonerRuleInterface $rule): ReasonerInterface
    {
        /** @var GraphPatternRule $rule */

        $pattern       = $rule->getPattern();
        $action        = $rule->getAction();
        $groupingRules = $rule->getGroupingRules();

        $groupingConditionHandlers = [];
        $groupsFilterHandlers = [];

        foreach ($groupingRules as $groupingRule) {
            foreach ($groupingRule->getConditions() as $groupingCondition) {
                $groupingConditionClass = get_class($groupingCondition);
                if (isset($groupingConditionHandlers[$groupingConditionClass])) {
                    continue;
                }

                $groupingConditionHandler = $this->groupingConditionsLocator->get($groupingCondition);

                $groupingConditionHandlers[$groupingConditionClass] = $groupingConditionHandler;
            }

            foreach ($groupingRule->getFilters() as $groupsFilter) {
                $groupsFilterClass = get_class($groupsFilter);
                if (isset($groupsFilterHandlers[$groupsFilterClass])) {
                    continue;
                }

                $groupsFilterHandler = $this->groupsFiltersLocator->get($groupsFilter);

                $groupsFilterHandlers[$groupsFilterClass] = $groupsFilterHandler;
            }
        }

        $mather        = $this->matchersLocator->get($pattern);
        $actionHandler = $this->actionsLocator->get($action);

        return new GraphPatternReasoner($pattern, $mather, $groupingRules, $groupingConditionHandlers, $groupsFilterHandlers, $action, $actionHandler);
    }
}