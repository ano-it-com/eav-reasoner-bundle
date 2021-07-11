<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\SameObject;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionInterface;

class SameObjectHandler implements GroupingConditionHandlerInterface
{

    public static function getSupportedCondition(): string
    {
        return SameObject::class;
    }


    public function getMatchKey(GroupingConditionInterface $condition, EAVEntityInterface $entity): string
    {
        return $entity->getId();
    }
}