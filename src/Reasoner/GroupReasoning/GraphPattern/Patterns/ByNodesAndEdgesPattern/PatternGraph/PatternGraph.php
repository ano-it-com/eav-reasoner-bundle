<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\ByNodesAndEdgesPattern;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;

class PatternGraph
{

    private array $nodes = [];

    private array $edges = [];


    public static function fromPattern(ByNodesAndEdgesPattern $pattern): self
    {
        $nodeSelectors = $pattern->getNodeSelectors();
        $edgeSelectors = $pattern->getEdgeSelectors();

        $graph = new self();
        foreach ($nodeSelectors as $nodeSelector) {
            $graph->createNode($nodeSelector);
        }

        foreach ($edgeSelectors as $edgeSelector) {
            $fromNode = $graph->getNodeByAlias($edgeSelector->getFromNodeAlias());
            $toNode   = $graph->getNodeByAlias($edgeSelector->getToNodeAlias());

            $graph->createEdge($fromNode, $toNode, $edgeSelector);
        }

        return $graph;
    }


    public function createNode(NodeSelectorInterface $nodeSelector): PatternNode
    {
        $alias = $nodeSelector->getAlias();

        if (isset($this->nodes[$alias])) {
            throw new \InvalidArgumentException('Node with alias ' . $alias . ' is already exists in pattern graph');
        }

        $node = new PatternNode($nodeSelector);

        $this->nodes[$nodeSelector->getAlias()] = $node;

        return $node;
    }


    public function getNodeByAlias(string $alias): PatternNode
    {
        $node = $this->nodes[$alias] ?? null;
        if ( ! $node) {
            throw new \InvalidArgumentException('Node with alias ' . $alias . ' not found in pattern graph');
        }

        return $node;
    }


    public function createEdge(PatternNode $fromNode, PatternNode $toNode, EdgeSelectorInterface $edgeSelector): PatternEdge
    {
        $uniqueKey = $edgeSelector->getUniqueKey();

        if (isset($this->edges[$uniqueKey])) {
            throw new \InvalidArgumentException('Edge with unique key  ' . $uniqueKey . ' is already exists in pattern graph');
        }

        $edge = new PatternEdge($fromNode, $toNode, $edgeSelector);

        $this->edges[$uniqueKey] = $edge;

        $fromNode->addEdge($edge);
        $toNode->addEdge($edge);

        return $edge;
    }


    /**
     * @return array<PatternNode>
     */
    public function getNodes(): array
    {
        return array_values($this->nodes);
    }


    /**
     * @return array<PatternEdge>
     */
    public function getEdges(): array
    {
        return array_values($this->edges);
    }
}