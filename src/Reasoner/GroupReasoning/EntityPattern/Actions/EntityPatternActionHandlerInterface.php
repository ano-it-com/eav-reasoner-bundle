<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups\MatchedGroups;

interface EntityPatternActionHandlerInterface
{

    /**
     * @param MatchedGroups                $matchedGroups
     * @param EntityPatternActionInterface $action
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return ActionResult
     */
    public function handle(MatchedGroups $matchedGroups, EntityPatternActionInterface $action, array $namespaces): ActionResult;


    public static function getSupportedAction(): string;
}