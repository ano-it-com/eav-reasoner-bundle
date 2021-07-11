<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\MergeNodes;

use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\AfterMergeEntitiesHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;

class MergeNodes implements GraphPatternActionInterface
{

    /**
     * @var string[]
     */
    private array $nodeAliases;

    private ?AfterMergeEntitiesHandlerInterface $afterMergeEntitiesHandler;


    public function __construct(array $nodeAliases, ?AfterMergeEntitiesHandlerInterface $afterMergeEntitiesHandler = null)
    {
        $this->nodeAliases               = $nodeAliases;
        $this->afterMergeEntitiesHandler = $afterMergeEntitiesHandler;
    }


    /**
     * @return string[]
     */
    public function getNodeAliases(): array
    {
        return $this->nodeAliases;
    }


    public function getAfterMergeEntitiesHandler(): ?AfterMergeEntitiesHandlerInterface
    {
        return $this->afterMergeEntitiesHandler;
    }
}