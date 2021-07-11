<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions;

use ANOITCOM\EAVReasonerBundle\Reasoner\ActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\AfterMergeEntitiesHandlerInterface;

interface GraphPatternActionInterface extends ActionInterface
{

    public function getAfterMergeEntitiesHandler(): ?AfterMergeEntitiesHandlerInterface;
}