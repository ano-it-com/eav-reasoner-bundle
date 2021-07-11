<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;

interface GroupingConditionHandlerInterface
{

    public static function getSupportedCondition(): string;


    public function getMatchKey(GroupingConditionInterface $condition, EAVEntityInterface $entity): ?string;
}