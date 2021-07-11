<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Reasoner;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GraphGroups;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Conditions\GroupingConditionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters\GroupsFilterHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\GroupingRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternMatcherInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerInterface;

class GraphPatternReasoner implements ReasonerInterface
{

    private GraphPatternInterface $pattern;

    private GraphPatternMatcherInterface $patternMatcher;

    /**
     * @var array<GroupingRule>
     */
    private array $groupingRules;

    /**
     * @var array<string,GroupingConditionHandlerInterface>
     */
    private array $groupingConditionHandlers;

    private GraphPatternActionInterface $action;

    private GraphPatternActionHandlerInterface $actionHandler;

    /**
     * @var array<string, GroupsFilterHandlerInterface>
     */
    private array $groupsFilterHandlers;


    /**
     * @param GraphPatternInterface                           $pattern
     * @param GraphPatternMatcherInterface                    $patternMatcher
     * @param array<GroupingRule>                             $groupingRules
     * @param array<string,GroupingConditionHandlerInterface> $groupingConditionHandlers
     * @param array<string, GroupsFilterHandlerInterface>     $groupsFilterHandlers
     * @param GraphPatternActionInterface                     $action
     * @param GraphPatternActionHandlerInterface              $actionHandler
     */
    public function __construct(
        GraphPatternInterface $pattern,
        GraphPatternMatcherInterface $patternMatcher,
        array $groupingRules,
        array $groupingConditionHandlers,
        array $groupsFilterHandlers,
        GraphPatternActionInterface $action,
        GraphPatternActionHandlerInterface $actionHandler
    ) {

        $this->pattern                   = $pattern;
        $this->patternMatcher            = $patternMatcher;
        $this->groupingRules             = $groupingRules;
        $this->groupingConditionHandlers = $groupingConditionHandlers;
        $this->groupsFilterHandlers      = $groupsFilterHandlers;
        $this->action                    = $action;
        $this->actionHandler             = $actionHandler;
    }


    /**
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return ActionResult
     */
    public function apply(array $namespaces): ActionResult
    {
        $graphGroups = new GraphGroups($this->groupingRules, $this->groupingConditionHandlers, $this->groupsFilterHandlers);

        $patternMatches = $this->patternMatcher->getPatternMatches($this->pattern, $namespaces);

        foreach ($patternMatches as $patternMatch) {
            $graphGroups->add($patternMatch);
        }

        $graphGroups->close();

        return $this->actionHandler->handle($graphGroups, $this->action, $namespaces);
    }
}