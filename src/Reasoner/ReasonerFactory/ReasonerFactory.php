<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory;

use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

class ReasonerFactory
{

    private ReasonerBuildersLocator $buildersLocator;


    public function __construct(ReasonerBuildersLocator $buildersLocator)
    {
        $this->buildersLocator = $buildersLocator;
    }


    public function build(ReasonerRuleInterface $rule): ReasonerInterface
    {
        $builder = $this->buildersLocator->get($rule);

        return $builder->build($rule);
    }

}