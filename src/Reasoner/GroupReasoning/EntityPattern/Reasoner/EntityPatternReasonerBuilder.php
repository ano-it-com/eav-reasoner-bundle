<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Reasoner;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityPatternRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternMatchersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory\ReasonerBuilderInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;

class EntityPatternReasonerBuilder implements ReasonerBuilderInterface
{

    private EntityPatternMatchersLocator $matchersLocator;

    private EntityPatternActionHandlersLocator $actionsLocator;


    public function __construct(EntityPatternMatchersLocator $matchersLocator, EntityPatternActionHandlersLocator $actionsLocator)
    {
        $this->matchersLocator = $matchersLocator;
        $this->actionsLocator  = $actionsLocator;
    }


    public function build(ReasonerRuleInterface $rule): ReasonerInterface
    {
        /** @var EntityPatternRule $rule */

        $pattern = $rule->getPattern();
        $action  = $rule->getAction();

        $mather        = $this->matchersLocator->get($pattern);
        $actionHandler = $this->actionsLocator->get($action);

        return new EntityPatternReasoner($pattern, $mather, $action, $actionHandler);
    }


    public static function supports(ReasonerRuleInterface $rule): bool
    {
        return $rule instanceof EntityPatternRule;
    }
}