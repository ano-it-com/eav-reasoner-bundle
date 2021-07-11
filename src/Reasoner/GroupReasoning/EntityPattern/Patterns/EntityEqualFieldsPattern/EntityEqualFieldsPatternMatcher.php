<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualFieldsPattern;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\CursorQuery\CursorQuery;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups\MatchedGroups;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternMatcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityEqualFieldsPatternMatcher implements EntityPatternMatcherInterface
{

    private EAVEntityManagerInterface $em;

    private EntityFilterHandlersLocator $filterHandlersLocator;


    public function __construct(EAVEntityManagerInterface $em, EntityFilterHandlersLocator $filterHandlersLocator)
    {
        $this->em                    = $em;
        $this->filterHandlersLocator = $filterHandlersLocator;
    }


    public function getMatchedGroups(EntityPatternInterface $pattern, array $namespaces = []): MatchedGroups
    {
        /** @var EntityEqualFieldsPattern $pattern */

        $matchedGroups = new MatchedGroups();

        $rows = $this->getMatchedRows($pattern, $namespaces);

        foreach ($rows as $row) {
            $matchedGroups->addToGroup((string)$row['equal_group'], $row['entity_id']);
        }

        return $matchedGroups;
    }


    public static function getSupportedPattern(): string
    {
        return EntityEqualFieldsPattern::class;
    }


    /**
     * @param EntityEqualFieldsPattern     $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $chunk
     *
     * @return \Generator
     */
    private function getMatchedRows(EntityEqualFieldsPattern $pattern, array $namespaces, int $chunk = 10000): \Generator
    {
        $qb = $this->createQuery($pattern, $namespaces);

        $cursorQuery = new CursorQuery($qb);

        return $cursorQuery->fetch($chunk);

    }


    /**
     * @param EntityEqualFieldsPattern     $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return QueryBuilder
     */
    private function createQuery(EntityEqualFieldsPattern $pattern, array $namespaces): QueryBuilder
    {
        $fields       = $pattern->getFields();
        $namespaceIds = array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces);

        $entitiesTable = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY);

        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->from($entitiesTable, $entitiesTable);

        $select       = [ $entitiesTable . '.id as entity_id' ];
        $fieldColumns = [];
        $fieldAliases = [];
        foreach ($fields as $field) {
            $columnName     = $entitiesTable . '.' . $field;
            $aliasName      = $entitiesTable . '_' . $field;
            $fieldColumns[] = $columnName;
            $fieldAliases[] = $aliasName;

            $select[] = $columnName . ' as ' . $aliasName;
        }

        if (count($namespaceIds)) {
            $qb->andWhere($entitiesTable . '.namespace_id in (:namespace)');

            $qb->setParameter('namespace', $namespaceIds, Connection::PARAM_STR_ARRAY);
        }

        $entityFilters = $pattern->getEntityFilters();
        if (count($entityFilters)) {

            foreach ($entityFilters as $i => $entityFilter) {
                $entityFilterHandler = $this->filterHandlersLocator->get($entityFilter);
                $entityFilterHandler->apply($entityFilter, $qb, [ $entitiesTable ], $i);
            }
        }

        $select[] = 'count(*) OVER (partition by ' . implode(', ', $fieldColumns) . ' order by ' . implode(', ', $fieldColumns) . ') as equal_in_group';
        $select[] = 'dense_rank() OVER (order by ' . implode(', ', $fieldColumns) . ') as equal_group';

        $qb->select($select);

        // external query

        $qbExternal = $this->em->getConnection()->createQueryBuilder();

        $qbExternal
            ->select([ 'entity_id', ...$fieldAliases, 'equal_in_group', 'equal_group' ])
            ->from('(' . $qb->getSQL() . ') as dt')
            ->andWhere('equal_in_group > 1')
            ->setParameters($qb->getParameters(), $qb->getParameterTypes());

        $sql    = $qbExternal->getSQL();
        $params = $qbExternal->getParameters();

        return $qbExternal;
    }
}