<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\NullAction;

use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\AfterMergeEntitiesHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;

class NullAction implements GraphPatternActionInterface
{

    public function getAfterMergeEntitiesHandler(): ?AfterMergeEntitiesHandlerInterface
    {
        return null;
    }
}