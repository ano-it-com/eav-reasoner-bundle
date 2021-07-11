<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\CountInGroup;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatchLight;

class CountInGroupHandler implements GroupsFilterHandlerInterface
{

    public static function getSupportedFilter(): string
    {
        return CountInGroup::class;
    }


    public function filter(GroupsFilterInterface $groupsFilter, array $groups): array
    {
        /** @var CountInGroup $groupsFilter */
        $nodeAlias = $groupsFilter->getNodeAlias();
        $operator  = $groupsFilter->getOperator();
        $value     = $groupsFilter->getValue();

        foreach ($groups as $i => $group) {
            $aliasIds = [];
            /** @var PatternMatchLight $patternMatchLight */
            foreach ($group as $patternMatchLight) {
                $aliasIds[] = $patternMatchLight->getEntityIdByAlias($nodeAlias);
            }

            $aliasIds = array_unique($aliasIds);

            if ( ! $this->isSatisfyConditions(count($aliasIds), $operator, $value)) {
                unset($groups[$i]);
            }
        }

        return $groups;
    }


    private function isSatisfyConditions(int $countInGroup, string $operator, int $value): bool
    {
        switch ($operator) {
            case '=':
                return $countInGroup === $value;
            case '<':
                return $countInGroup < $value;
            case '<=':
                return $countInGroup <= $value;
            case '>':
                return $countInGroup > $value;
            case '>=':
                return $countInGroup >= $value;
            default:
                throw new \InvalidArgumentException('Operator \'' . $operator . '\' is unknown. Supported ara =, <, <=, >, >=.');
        }
    }
}