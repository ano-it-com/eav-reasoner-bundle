<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorInterface;

class PatternEdge
{

    private PatternNode $fromNode;

    private PatternNode $toNode;

    private EdgeSelectorInterface $edgeSelector;

    private string $uniqueKey;

    private ?string $edgeTableAlias = null;

    private bool $processed = false;


    public function __construct(PatternNode $fromNode, PatternNode $toNode, EdgeSelectorInterface $edgeSelector)
    {

        $this->fromNode     = $fromNode;
        $this->toNode       = $toNode;
        $this->edgeSelector = $edgeSelector;
        $this->uniqueKey    = $edgeSelector->getUniqueKey();
    }


    public function getFromNode(): PatternNode
    {
        return $this->fromNode;
    }


    public function getToNode(): PatternNode
    {
        return $this->toNode;
    }


    public function getEdgeSelector(): EdgeSelectorInterface
    {
        return $this->edgeSelector;
    }


    public function getUniqueKey(): string
    {
        return $this->uniqueKey;
    }


    public function getTableAlias(): string
    {
        if ( ! $this->edgeTableAlias) {
            throw new \RuntimeException('Table alias doesn\'t set yet');
        }

        return $this->edgeTableAlias;
    }


    public function isProcessed(): bool
    {
        return $this->processed;
    }


    public function setProcessed(string $edgeTableAlias): void
    {
        $this->edgeTableAlias = $edgeTableAlias;
        $this->processed      = true;
    }


    public function getIdColumnAlias(): string
    {
        $tableAlias = $this->getTableAlias();

        return $tableAlias . '_id';
    }

}