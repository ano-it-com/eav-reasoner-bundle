<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory;

use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

interface ReasonerBuilderInterface
{

    public static function supports(ReasonerRuleInterface $rule): bool;


    public function build(ReasonerRuleInterface $rule): ReasonerInterface;
}