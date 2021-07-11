<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\EqualByProperties;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionInterface;

class EqualByPropertiesHandler implements GroupingConditionHandlerInterface
{

    public static function getSupportedCondition(): string
    {
        return EqualByProperties::class;
    }


    /** TODO - подумать, как буду сравниваться две сущности у которых есть совпадающие значения свойств, но совпадают не все.
     * Например, у одной сущности фамилия Иванов, а у второй Иванов и Иванов1
     */
    public function getMatchKey(GroupingConditionInterface $condition, EAVEntityInterface $entity): string
    {
        /** @var EqualByProperties $condition */

        $equalPropertyGroups = $condition->getEqualPropertyGroups();

        $keyParts = [];

        foreach ($equalPropertyGroups as $equalPropertyGroup) {
            $propertyGroupValues = [];
            $propertyIds         = $equalPropertyGroup->getPropertyIds();

            foreach ($entity->getValues() as $value) {
                if (in_array($value->getTypePropertyId(), $propertyIds, true)) {
                    $propertyGroupValues[] = $value->getValueAsString();
                }
            }

            sort($propertyGroupValues);

            $keyParts[] = implode(':', $propertyGroupValues);
        }

        return implode('-', $keyParts);
    }
}
