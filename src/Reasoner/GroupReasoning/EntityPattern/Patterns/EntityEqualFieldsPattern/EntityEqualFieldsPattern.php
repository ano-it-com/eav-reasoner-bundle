<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualFieldsPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;

class EntityEqualFieldsPattern implements EntityPatternInterface
{

    /**
     * @var array<string>
     */
    private array $fields;

    private array $entityFilters;


    /**
     * EntityEqualFieldsPattern constructor.
     *
     * @param array                        $fields
     * @param array<EntityFilterInterface> $entityFilters
     */
    public function __construct(array $fields, array $entityFilters)
    {
        $this->fields        = $fields;
        $this->entityFilters = $entityFilters;
    }


    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }


    /**
     * @return \ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface[]
     */
    public function getEntityFilters(): array
    {
        return $this->entityFilters;
    }
}