<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\Selectors;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorInterface;

class EdgeByType implements EdgeSelectorInterface
{

    private string $fromNodeAlias;

    private array $relationTypeIds;

    private string $toNodeAlias;

    private string $uniqueKey;


    public function __construct(string $fromNodeAlias, array $relationTypeIds, string $toNodeAlias)
    {
        $this->fromNodeAlias = $fromNodeAlias;
        sort($relationTypeIds);
        $this->relationTypeIds = $relationTypeIds;
        $this->toNodeAlias     = $toNodeAlias;
        $this->uniqueKey       = $this->fromNodeAlias . md5(implode('_', $this->relationTypeIds)) . $this->toNodeAlias;
    }


    public function getFromNodeAlias(): string
    {
        return $this->fromNodeAlias;
    }


    public function getToNodeAlias(): string
    {
        return $this->toNodeAlias;
    }


    public function getUniqueKey(): string
    {
        return $this->uniqueKey;
    }


    public function getRelationTypeIds(): array
    {
        return $this->relationTypeIds;
    }
}