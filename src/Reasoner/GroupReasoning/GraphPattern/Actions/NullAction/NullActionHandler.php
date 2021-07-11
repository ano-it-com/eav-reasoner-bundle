<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\NullAction;

use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GraphGroups;

class NullActionHandler implements GraphPatternActionHandlerInterface
{

    public function handle(GraphGroups $graphGroups, GraphPatternActionInterface $action, array $namespaces): ActionResult
    {
        return new ActionResult();
    }


    public static function getSupportedAction(): string
    {
        return NullAction::class;
    }
}