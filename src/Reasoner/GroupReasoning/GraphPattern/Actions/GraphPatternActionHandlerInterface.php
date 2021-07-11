<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GraphGroups;

interface GraphPatternActionHandlerInterface
{

    /**
     * @param GraphGroups                  $graphGroups
     * @param GraphPatternActionInterface  $action
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return ActionResult
     */
    public function handle(GraphGroups $graphGroups, GraphPatternActionInterface $action, array $namespaces): ActionResult;


    public static function getSupportedAction(): string;
}