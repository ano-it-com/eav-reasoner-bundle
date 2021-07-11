<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\EqualByProperties;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common\EqualPropertyGroup;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionInterface;

class EqualByProperties implements GroupingConditionInterface
{

    /**
     * @var EqualPropertyGroup[]
     */
    private array $equalPropertyGroups;


    /**
     * @param array<EqualPropertyGroup> $equalPropertyGroups
     */
    public function __construct(array $equalPropertyGroups)
    {
        $this->equalPropertyGroups = $equalPropertyGroups;
    }


    /**
     * @return EqualPropertyGroup[]
     */
    public function getEqualPropertyGroups(): array
    {
        return $this->equalPropertyGroups;
    }
}