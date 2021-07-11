<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common;

class EqualPropertyGroup
{

    private array $propertyIds;


    public function __construct(array $propertyIds)
    {
        $this->propertyIds = $propertyIds;
    }


    /**
     * @return array<string>
     */
    public function getPropertyIds(): array
    {
        return $this->propertyIds;
    }
}