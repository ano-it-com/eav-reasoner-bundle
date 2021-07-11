<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;

class PatternNode
{

    private NodeSelectorInterface $nodeSelector;

    /** @var array<PatternEdge> */
    private array $edges = [];

    private bool $processed = false;

    private ?string $nodeTableAlias = null;


    public function __construct(NodeSelectorInterface $nodeSelector)
    {
        $this->nodeSelector = $nodeSelector;
    }


    /**
     * @param PatternEdge $edge
     *
     * @internal
     */
    public function addEdge(PatternEdge $edge): void
    {
        if ($edge->getFromNode() !== $this && $edge->getToNode() !== $this) {
            throw new \InvalidArgumentException('Edge is not related to this node');
        }

        $this->edges[$edge->getUniqueKey()] = $edge;
    }


    public function getNodeSelector(): NodeSelectorInterface
    {
        return $this->nodeSelector;
    }


    /**
     * @return array<PatternEdge>
     */
    public function getOutgoingEdges(): array
    {
        return array_values(array_filter($this->edges, function (PatternEdge $edge) {
            return $edge->getFromNode() === $this;
        }));
    }


    /**
     * @return array<PatternEdge>
     */
    public function getEdges(): array
    {
        return array_values($this->edges);
    }


    /**
     * @return array<PatternEdge>
     */
    public function getIncomingEdges(): array
    {
        return array_values(array_filter($this->edges, function (PatternEdge $edge) {
            return $edge->getToNode() === $this;
        }));
    }


    public function isProcessed(): bool
    {
        return $this->processed;
    }


    public function setProcessed(string $nodeTableAlias): void
    {
        $this->nodeTableAlias = $nodeTableAlias;
        $this->processed      = true;
    }


    public function getTableAlias(): string
    {
        if ( ! $this->nodeTableAlias) {
            throw new \RuntimeException('Table alias doesn\'t set yet');
        }

        return $this->nodeTableAlias;
    }


    public function getIdColumnAlias(): string
    {
        $tableAlias = $this->getTableAlias();

        return $tableAlias . '_id';
    }

}