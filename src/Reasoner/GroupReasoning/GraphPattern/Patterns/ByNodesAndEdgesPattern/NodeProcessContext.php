<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternEdge;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternNode;

class NodeProcessContext
{

    public const EDGE_FROM = 'from';
    public const EDGE_TO = 'to';

    private PatternNode $node;

    private ?PatternEdge $edge;

    private ?string $edgeSide;


    public function __construct(PatternNode $node, ?PatternEdge $edge = null, ?string $edgeSide = null)
    {
        $this->node     = $node;
        $this->edge     = $edge;
        $this->edgeSide = $edgeSide;

        if ($this->edge && ! $this->getEdge()) {
            throw new \InvalidArgumentException('If Edge is defined EdgeSide must be defined too');
        }
    }


    public function getNode(): PatternNode
    {
        return $this->node;
    }


    public function getEdge(): ?PatternEdge
    {
        return $this->edge;
    }


    public function getEdgeSide(): ?string
    {
        return $this->edgeSide;
    }


    public function getNodeSelector(): NodeSelectorInterface
    {
        return $this->node->getNodeSelector();
    }


    public static function getNodePositionSide(PatternNode $node, PatternEdge $edge): string
    {
        return $edge->getFromNode() === $node ? NodeProcessContext::EDGE_FROM : NodeProcessContext::EDGE_TO;
    }
}