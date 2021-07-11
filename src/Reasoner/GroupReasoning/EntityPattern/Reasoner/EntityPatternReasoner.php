<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Reasoner;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternMatcherInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;

class EntityPatternReasoner implements ReasonerInterface
{

    private EntityPatternInterface $pattern;

    private EntityPatternMatcherInterface $patternMatcher;

    private EntityPatternActionInterface $action;

    private EntityPatternActionHandlerInterface $actionHandler;


    public function __construct(
        EntityPatternInterface $pattern,
        EntityPatternMatcherInterface $patternMatcher,
        EntityPatternActionInterface $action,
        EntityPatternActionHandlerInterface $actionHandler
    ) {

        $this->pattern        = $pattern;
        $this->patternMatcher = $patternMatcher;
        $this->action         = $action;
        $this->actionHandler  = $actionHandler;
    }


    /**
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return ActionResult
     */
    public function apply(array $namespaces): ActionResult
    {
        $matchedGroups = $this->patternMatcher->getMatchedGroups($this->pattern, $namespaces);

        return $this->actionHandler->handle($matchedGroups, $this->action, $namespaces);
    }
}