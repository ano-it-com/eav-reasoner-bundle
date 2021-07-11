<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\Selectors;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeFilterHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node\NodeSelectorInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\NodeProcessContext;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class NodeByTypeHandler implements NodeSelectorHandlerInterface
{

    private NodeFilterHandlersLocator $filterHandlersLocator;

    private string $entitiesTable;


    public function __construct(EAVEntityManagerInterface $em, NodeFilterHandlersLocator $filterHandlersLocator)
    {
        $this->entitiesTable         = $em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY);
        $this->filterHandlersLocator = $filterHandlersLocator;
    }


    public static function getSupportedSelector(): string
    {
        return NodeByType::class;
    }


    public function handle(NodeProcessContext $context, QueryBuilder $qb, array $namespaces, int $conditionIndex): void
    {
        $node = $context->getNode();

        $nodeTableAlias = $this->makeTableAlias($context, $conditionIndex);

        if ($this->isFirstHandledNode($context)) {
            $this->handleFirstNode($context, $qb, $nodeTableAlias, $namespaces, $conditionIndex);
        } else {
            $this->handleJoinNode($context, $qb, $nodeTableAlias, $namespaces, $conditionIndex);

        }

        $node->setProcessed($nodeTableAlias);
    }


    private function isFirstHandledNode(NodeProcessContext $context): bool
    {
        return $context->getEdge() === null;
    }


    /**
     * @param NodeProcessContext           $context
     * @param QueryBuilder                 $qb
     * @param string                       $nodeTableAlias
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $conditionIndex
     */
    public function handleFirstNode(NodeProcessContext $context, QueryBuilder $qb, string $nodeTableAlias, array $namespaces, int $conditionIndex): void
    {
        $qb->from($this->entitiesTable, $nodeTableAlias);

        /** @var NodeByType $nodeSelector */
        $nodeSelector = $context->getNodeSelector();
        $this->applyTypesCondition($qb, $nodeTableAlias, $nodeSelector->getTypeIds(), $conditionIndex);

        $namespaceIds = array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces);
        $this->applyNamespacesCondition($qb, $nodeTableAlias, $namespaceIds, $conditionIndex);

        $this->applyNodeFilters($qb, $nodeSelector, $nodeTableAlias, $namespaces);
    }


    /**
     * @param NodeProcessContext           $context
     * @param QueryBuilder                 $qb
     * @param string                       $nodeTableAlias
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $conditionIndex
     */
    public function handleJoinNode(NodeProcessContext $context, QueryBuilder $qb, string $nodeTableAlias, array $namespaces, int $conditionIndex): void
    {
        $edge = $context->getEdge();

        if ( ! $edge) {
            throw new \RuntimeException('Edge is required for join node processing. Probably it\'s first node and it must be processed separately.');
        }

        $edgeTableAlias  = $edge->getTableAlias();
        $edgeTableColumn = $context->getEdgeSide() === NodeProcessContext::EDGE_FROM ? 'from_id' : 'to_id';

        $qb->leftJoin($edgeTableAlias, $this->entitiesTable, $nodeTableAlias, $edgeTableAlias . '.' . $edgeTableColumn . ' = ' . $nodeTableAlias . '.id');

        $nodeSelector = $context->getNodeSelector();
        $this->applyTypesCondition($qb, $nodeTableAlias, $nodeSelector->getTypeIds(), $conditionIndex);

        $namespaceIds = array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces);
        $this->applyNamespacesCondition($qb, $nodeTableAlias, $namespaceIds, $conditionIndex);

        $this->applyNodeFilters($qb, $nodeSelector, $nodeTableAlias, $namespaces);
    }


    /**
     * @param QueryBuilder  $qb
     * @param string        $nodeTableAlias
     * @param array<string> $typeIds
     * @param int           $conditionIndex
     */
    private function applyTypesCondition(QueryBuilder $qb, string $nodeTableAlias, array $typeIds, int $conditionIndex): void
    {
        $typesParamName = 'nodeTypes_' . $conditionIndex;
        $qb->andWhere($nodeTableAlias . '.type_id IN (:' . $typesParamName . ')')->setParameter($typesParamName, $typeIds, Connection::PARAM_STR_ARRAY);
    }


    /**
     * @param QueryBuilder  $qb
     * @param string        $nodeTableAlias
     * @param array<string> $namespaceIds
     * @param int           $conditionIndex
     */
    private function applyNamespacesCondition(QueryBuilder $qb, string $nodeTableAlias, array $namespaceIds, int $conditionIndex): void
    {
        $namespacesParamName = 'nodeNamespaces_' . $conditionIndex;
        $qb->andWhere($nodeTableAlias . '.namespace_id IN (:' . $namespacesParamName . ')')->setParameter($namespacesParamName, $namespaceIds, Connection::PARAM_STR_ARRAY);
    }


    /**
     * @param QueryBuilder                 $qb
     * @param NodeSelectorInterface        $nodeSelector
     * @param string                       $nodeTableAlias
     * @param array<EAVNamespaceInterface> $namespaces
     */
    private function applyNodeFilters(QueryBuilder $qb, NodeSelectorInterface $nodeSelector, string $nodeTableAlias, array $namespaces): void
    {
        foreach ($nodeSelector->getNodeFilters() as $filterIndex => $nodeFilter) {
            $nodeFilterHandler = $this->filterHandlersLocator->get($nodeFilter);

            $nodeFilterHandler->handle($nodeFilter, $qb, $nodeTableAlias, $namespaces, $filterIndex);
        }
    }


    private function makeTableAlias(NodeProcessContext $context, int $index): string
    {
        return 'e_' . $context->getNodeSelector()->getAlias() . $index;
    }

}