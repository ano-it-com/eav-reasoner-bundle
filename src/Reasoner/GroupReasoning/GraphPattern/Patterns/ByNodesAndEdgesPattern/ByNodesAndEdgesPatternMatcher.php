<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\CursorQuery\CursorQuery;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Edge\EdgeSelectorHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternGraph;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternGraph\PatternNode;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\PatternMatchFactory\PatternMatchesFactory;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\GraphPatternMatcherInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class ByNodesAndEdgesPatternMatcher implements GraphPatternMatcherInterface
{

    private EAVEntityManagerInterface $em;

    private NodeSelectorHandlersLocator $nodeSelectorHandlersLocator;

    private EdgeSelectorHandlersLocator $edgeSelectorHandlersLocator;

    private NumbersSequence $numbersSequence;

    private PatternMatchesFactory $patternMatchesFactory;


    public function __construct(
        EAVEntityManagerInterface $em,
        NodeSelectorHandlersLocator $nodeSelectorHandlersLocator,
        EdgeSelectorHandlersLocator $edgeSelectorHandlersLocator,
        PatternMatchesFactory $patternMatchesFactory
    ) {
        $this->em                          = $em;
        $this->nodeSelectorHandlersLocator = $nodeSelectorHandlersLocator;
        $this->edgeSelectorHandlersLocator = $edgeSelectorHandlersLocator;
        $this->numbersSequence             = new NumbersSequence();
        $this->patternMatchesFactory       = $patternMatchesFactory;
    }


    public static function getSupportedPattern(): string
    {
        return ByNodesAndEdgesPattern::class;
    }


    public function getPatternMatches(GraphPatternInterface $pattern, array $namespaces = []): \Generator
    {
        /** @var ByNodesAndEdgesPattern $pattern */

        $chunkSize = 5000;

        $patternGraph = PatternGraph::fromPattern($pattern);

        $rows = $this->getMatchedRows($patternGraph, $namespaces, $chunkSize);

        $rowsBatch = [];
        foreach ($rows as $row) {
            $rowsBatch[] = $row;

            if (count($rowsBatch) >= $chunkSize) {
                yield from $this->patternMatchesFactory->fromRows($rowsBatch, $patternGraph);
                $rowsBatch = [];
            }
        }

        if (count($rowsBatch) >= 0) {
            yield from $this->patternMatchesFactory->fromRows($rowsBatch, $patternGraph);
        }

    }


    public function getMatchedRows(PatternGraph $patternGraph, array $namespaces, int $chunk = 10000): \Generator
    {
        $qb = $this->createQuery($patternGraph, $namespaces);

        $cursorQuery = new CursorQuery($qb);

        return $cursorQuery->fetch($chunk);
    }


    /**
     * @param PatternGraph                 $patternGraph
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return QueryBuilder
     */
    private function createQuery(PatternGraph $patternGraph, array $namespaces): QueryBuilder
    {
        $qb = $this->em->getConnection()->createQueryBuilder();

        /** BFS - breadth-first search */
        /** @var array<NodeProcessContext> $queue */
        $queue   = [];
        $queue[] = new NodeProcessContext($patternGraph->getNodes()[0], $edge = null, $edgeSide = null);

        while (count($queue)) {
            $context = array_shift($queue);

            if ($context->getNode()->isProcessed()) {
                continue;
            }

            $newContexts = $this->processNode($qb, $context, $namespaces);

            if (count($newContexts)) {
                array_push($queue, ...$newContexts);
            }

        }

        $this->applySelectStatements($qb, $patternGraph);
        $this->applyGroupByStatements($qb, $patternGraph);

        // добавить условия по нодам со степенью > 1
        $this->applyMultiplePowerNodesClause($qb, $patternGraph);

        $sql    = $qb->getSQL();
        $params = $qb->getParameters();

        return $qb;
    }


    /**
     * @param QueryBuilder                 $qb
     * @param NodeProcessContext           $context
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return array<PatternNode>
     */
    private function processNode(QueryBuilder $qb, NodeProcessContext $context, array $namespaces): array
    {
        $nodeSelector = $context->getNodeSelector();
        $nodeHandler  = $this->nodeSelectorHandlersLocator->get($nodeSelector);

        $nodeHandler->handle($context, $qb, $namespaces, $this->numbersSequence->getNext());

        $node  = $context->getNode();
        $edges = $node->getEdges();

        $newContexts = [];
        foreach ($edges as $edge) {
            if ($edge->isProcessed()) {
                continue;
            }
            $edgeHandler = $this->edgeSelectorHandlersLocator->get($edge->getEdgeSelector());

            $edgeIndex = $this->numbersSequence->getNext();
            $edgeHandler->handle($edge, $node, $qb, $namespaces, $edgeIndex);

            $currentNodeEdgeSide = NodeProcessContext::getNodePositionSide($node, $edge);
            $newNodeEdgeSide     = $currentNodeEdgeSide === NodeProcessContext::EDGE_FROM ? NodeProcessContext::EDGE_TO : NodeProcessContext::EDGE_FROM;

            $newNode = ($newNodeEdgeSide === NodeProcessContext::EDGE_FROM) ? $edge->getFromNode() : $edge->getToNode();

            $newContexts[] = new NodeProcessContext($newNode, $edge, $newNodeEdgeSide);
        }

        return $newContexts;
    }


    private function applySelectStatements(QueryBuilder $qb, PatternGraph $patternGraph): void
    {
        $select = [];
        foreach ($patternGraph->getNodes() as $node) {
            $nodeTableAlias = $node->getTableAlias();

            $select[] = $nodeTableAlias . '.id as ' . $node->getIdColumnAlias();

            foreach ($node->getEdges() as $edge) {
                $edgeTableAlias = $edge->getTableAlias();

                $edgeSelectStmt = $edgeTableAlias . '.id as ' . $edge->getIdColumnAlias();

                if ( ! in_array($edgeSelectStmt, $select, true)) {
                    $select[] = $edgeSelectStmt;
                }
            }
        }

        $qb->select($select);
    }


    private function applyGroupByStatements(QueryBuilder $qb, PatternGraph $patternGraph): void
    {
        $groupBy = [];
        foreach ($patternGraph->getNodes() as $node) {
            $groupBy[] = $node->getIdColumnAlias();

            foreach ($node->getEdges() as $edge) {
                $edgeGroupByStmt = $edge->getIdColumnAlias();

                if ( ! in_array($edgeGroupByStmt, $groupBy, true)) {
                    $groupBy[] = $edgeGroupByStmt;
                }
            }
        }

        $qb->groupBy($groupBy);
    }


    private function applyMultiplePowerNodesClause(QueryBuilder $qb, PatternGraph $patternGraph): void
    {

        $parts = [];

        $toProcess = [
            'to_id'   => function (PatternNode $node) { return $node->getIncomingEdges(); },
            'from_id' => function (PatternNode $node) { return $node->getOutgoingEdges(); },
        ];

        foreach ($toProcess as $column => $edgesProvider) {
            foreach ($patternGraph->getNodes() as $node) {
                $edges = $edgesProvider($node);
                if (count($edges) <= 1) {
                    continue;
                }

                $edgesCount = count($edges);
                for ($i = 0; $i < $edgesCount - 1; $i++) {
                    $currentEdge = $edges[$i];
                    $nextEdge    = $edges[$i + 1];
                    $parts[]     = $currentEdge->getTableAlias() . '.' . $column . ' = ' . $nextEdge->getTableAlias() . '.' . $column;
                }
            }
        }

        $qb->andWhere(implode(' AND ', $parts));

    }

}