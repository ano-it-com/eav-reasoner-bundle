<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters;

interface GroupsFilterHandlerInterface
{

    public static function getSupportedFilter(): string;


    public function filter(GroupsFilterInterface $groupsFilter, array $groups): array;
}