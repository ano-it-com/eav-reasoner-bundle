<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\SingleTypeMerge;

use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\AfterMergeEntitiesHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionInterface;

class SingleTypeMerge implements EntityPatternActionInterface
{

    private ?AfterMergeEntitiesHandlerInterface $afterMergeEntitiesHandler;


    public function __construct(?AfterMergeEntitiesHandlerInterface $afterMergeEntitiesHandler = null)
    {
        $this->afterMergeEntitiesHandler = $afterMergeEntitiesHandler;
    }


    public function getAfterMergeEntitiesHandler(): ?AfterMergeEntitiesHandlerInterface
    {
        return $this->afterMergeEntitiesHandler;
    }

}