<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions;

use ANOITCOM\EAVReasonerBundle\Reasoner\ActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\AfterMergeEntitiesHandlerInterface;

interface EntityPatternActionInterface extends ActionInterface
{

    public function getAfterMergeEntitiesHandler(): ?AfterMergeEntitiesHandlerInterface;
}