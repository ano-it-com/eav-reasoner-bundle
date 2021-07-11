<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\Selectors;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeFilterInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;

class NodeByType implements NodeSelectorInterface
{

    private array $typeIds;

    /**
     * @var array<NodeFilterInterface>
     */
    private array $nodeFilters;

    private string $nodeAlias;


    /**
     * @param array                      $typeIds
     * @param string                     $nodeAlias
     * @param array<NodeFilterInterface> $nodeFilters
     */
    public function __construct(array $typeIds, string $nodeAlias, array $nodeFilters = [])
    {

        $this->typeIds     = $typeIds;
        $this->nodeFilters = $nodeFilters;
        $this->nodeAlias   = $nodeAlias;
    }


    /**
     * @return NodeFilterInterface[]
     */
    public function getNodeFilters(): array
    {
        return $this->nodeFilters;
    }


    public function getAlias(): string
    {
        return $this->nodeAlias;
    }


    public function getTypeIds(): array
    {
        return $this->typeIds;
    }

}