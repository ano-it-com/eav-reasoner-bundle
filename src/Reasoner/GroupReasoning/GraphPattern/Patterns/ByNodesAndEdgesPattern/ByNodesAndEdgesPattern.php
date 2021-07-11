<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternInterface;

class ByNodesAndEdgesPattern implements GraphPatternInterface
{

    /** @var array<NodeSelectorInterface> */
    private array $nodeSelectors;

    /** @var array<EdgeSelectorInterface> */
    private array $edgeSelectors;


    public function __construct(array $nodeSelectors, array $edgeSelectors)
    {

        $this->nodeSelectors = $nodeSelectors;
        $this->edgeSelectors = $edgeSelectors;
    }


    /**
     * @return NodeSelectorInterface[]
     */
    public function getNodeSelectors(): array
    {
        return $this->nodeSelectors;
    }


    /**
     * @return EdgeSelectorInterface[]
     */
    public function getEdgeSelectors(): array
    {
        return $this->edgeSelectors;
    }
}