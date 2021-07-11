<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\Filters;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface;

class EntityFieldEqualsFilter implements EntityFilterInterface
{

    private string $field;

    private array $values;


    /**
     * EntityFieldEqualsCondition constructor.
     *
     * @param string               $field
     * @param string|array<string> $value
     */
    public function __construct(string $field, $value)
    {
        $this->field = $field;
        if ( ! is_array($value)) {
            $this->values   = [];
            $this->values[] = $value;
        } else {
            $this->values = $value;
        }
    }


    public function getField(): string
    {
        return $this->field;
    }


    /**
     * @return array<string>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}