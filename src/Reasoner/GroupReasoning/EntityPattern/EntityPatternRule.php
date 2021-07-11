<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

class EntityPatternRule implements ReasonerRuleInterface
{

    private EntityPatternInterface $pattern;

    private EntityPatternActionInterface $action;


    public function __construct(
        EntityPatternInterface $pattern,
        EntityPatternActionInterface $action
    ) {

        $this->pattern = $pattern;
        $this->action  = $action;
    }


    public function getPattern(): EntityPatternInterface
    {
        return $this->pattern;
    }


    public function getAction(): EntityPatternActionInterface
    {
        return $this->action;
    }
}