<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualPropertiesPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common\EqualPropertyGroup;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;

class EntityEqualPropertiesPattern implements EntityPatternInterface
{

    private array $equalPropertyGroups;

    private array $entityFilters;


    /**
     * @param array<EqualPropertyGroup>                                                                                    $equalPropertyGroups
     * @param array<\ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface> $entityFilters
     */
    public function __construct(array $equalPropertyGroups, array $entityFilters)
    {
        if ( ! count($equalPropertyGroups)) {
            throw new \InvalidArgumentException('EqualPropertyGroups cannot be empty');
        }

        foreach ($equalPropertyGroups as $equalPropertyGroup) {
            if ( ! $equalPropertyGroup instanceof EqualPropertyGroup) {
                throw new \InvalidArgumentException('First argument must be array of EqualPropertyGroup, \'' . get_class($equalPropertyGroup) . '\' given');
            }
        }

        foreach ($entityFilters as $entityFieldCondition) {
            if ( ! $entityFieldCondition instanceof EntityFilterInterface) {
                throw new \InvalidArgumentException('First argument must be array of EntityFilterInterface, \'' . get_class($entityFieldCondition) . '\' given');
            }
        }

        $this->equalPropertyGroups = $equalPropertyGroups;
        $this->entityFilters       = $entityFilters;
    }


    public function getEqualPropertyGroups(): array
    {
        return $this->equalPropertyGroups;
    }


    /**
     * @return array<\ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface>
     */
    public function getEntityFilters(): array
    {
        return $this->entityFilters;
    }
}