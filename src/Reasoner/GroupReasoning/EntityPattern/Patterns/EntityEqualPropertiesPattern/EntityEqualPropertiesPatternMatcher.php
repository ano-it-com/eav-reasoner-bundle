<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualPropertiesPattern;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\TypeFilters\TypeProperty\TypePropertyFilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\DBAL\CursorQuery\CursorQuery;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\Settings\EAVSettings;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common\EqualPropertyGroup;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterHandlersLocator;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups\MatchedGroups;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityPatternMatcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityEqualPropertiesPatternMatcher implements EntityPatternMatcherInterface
{

    private EAVEntityManagerInterface $em;

    private EAVTypeRepositoryInterface $typeRepository;

    private EntityFilterHandlersLocator $filterHandlersLocator;


    public function __construct(EAVEntityManagerInterface $em, EAVTypeRepositoryInterface $typeRepository, EntityFilterHandlersLocator $filterHandlersLocator)
    {
        $this->em                    = $em;
        $this->typeRepository        = $typeRepository;
        $this->filterHandlersLocator = $filterHandlersLocator;
    }


    public function getMatchedGroups(EntityPatternInterface $pattern, array $namespaces = []): MatchedGroups
    {
        /** @var EntityEqualPropertiesPattern $pattern */

        $matchedGroups = new MatchedGroups();

        $rows = $this->getMatchedRows($pattern, $namespaces);

        foreach ($rows as $row) {
            $matchedGroups->addToGroup((string)$row['equal_group'], $row['entity_id']);
        }

        return $matchedGroups;
    }


    public static function getSupportedPattern(): string
    {
        return EntityEqualPropertiesPattern::class;
    }


    /**
     * @param EntityEqualPropertiesPattern $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     * @param int                          $chunk
     *
     * @return \Generator
     */
    private function getMatchedRows(EntityEqualPropertiesPattern $pattern, array $namespaces, int $chunk = 10000): \Generator
    {
        $qb = $this->createQuery($pattern, $namespaces);

        $cursorQuery = new CursorQuery($qb);

        return $cursorQuery->fetch($chunk);

    }


    /**
     * @param EntityEqualPropertiesPattern $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return QueryBuilder
     */
    private function createQuery(EntityEqualPropertiesPattern $pattern, array $namespaces): QueryBuilder
    {
        // props to search
        $equalPropertyGroups = $pattern->getEqualPropertyGroups();
        $namespaceIds        = array_map(static function (EAVNamespaceInterface $namespace) { return $namespace->getId(); }, $namespaces);

        $groupToColumnMapping = $this->makeGroupToColumnMapping($equalPropertyGroups);

        $valuesTable   = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::VALUES);
        $entitiesTable = $this->em->getEavSettings()->getTableNameForEntityType(EAVSettings::ENTITY);

        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->from($entitiesTable, $entitiesTable);

        $select        = [ $entitiesTable . '.id as entity_id' ];
        $valuesColumns = [];
        $valuesAliases = [];

        foreach ($equalPropertyGroups as $groupId => $propertyGroup) {
            $valuesAlias = 'values' . $groupId;
            $qb->leftJoin($entitiesTable, $valuesTable, $valuesAlias, $entitiesTable . '.id = ' . $valuesAlias . '.entity_id');
            $columnName = $groupToColumnMapping[$groupId];

            $valuesColumnName = $valuesAlias . '.' . $columnName;
            $valuesAliasName  = $valuesAlias . '_' . $columnName;

            $valuesColumns[] = $valuesColumnName;
            $valuesAliases[] = $valuesAliasName;

            $select[] = $valuesColumnName . ' as ' . $valuesAliasName;
            $qb->andWhere($valuesColumnName . ' is not null');

            $paramName = 'property_group_' . $groupId;
            $qb->andWhere($valuesAlias . '.type_property_id in (:' . $paramName . ')');

            $propertyIds = $propertyGroup->getPropertyIds();
            $qb->setParameter($paramName, $propertyIds, Connection::PARAM_STR_ARRAY);

            if (count($namespaceIds)) {
                $qb->andWhere($valuesAlias . '.namespace_id in (:namespace)');
            }
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

        $select[] = 'count(*) OVER (partition by ' . implode(', ', $valuesColumns) . ' order by ' . implode(', ', $valuesColumns) . ') as equal_in_group';
        $select[] = 'dense_rank() OVER (order by ' . implode(', ', $valuesColumns) . ') as equal_group';

        $qb->select($select);

        // external query

        $qbExternal = $this->em->getConnection()->createQueryBuilder();

        $qbExternal
            ->select([ 'entity_id', ...$valuesAliases, 'equal_in_group', 'equal_group' ])
            ->from('(' . $qb->getSQL() . ') as dt')
            ->andWhere('equal_in_group > 1')
            ->setParameters($qb->getParameters(), $qb->getParameterTypes());

        $sql    = $qbExternal->getSQL();
        $params = $qbExternal->getParameters();

        return $qbExternal;
    }


    /**
     * @param array<EqualPropertyGroup> $equalPropertyGroups
     *
     * @return array<int,string>
     */
    private function makeGroupToColumnMapping(array $equalPropertyGroups): array
    {
        $propertyIds = [];

        foreach ($equalPropertyGroups as $group) {
            foreach ($group->getPropertyIds() as $propertyId) {
                $propertyIds[] = $propertyId;
            }
        }

        $usedTypes = $this->typeRepository->findBy([ (new TypePropertyFilterCriteria())->whereIn('id', $propertyIds) ]);

        $propertiesById = [];

        /** @var EAVTypeInterface $type */
        foreach ($usedTypes as $type) {
            foreach ($type->getProperties() as $property) {
                $propertyId = $property->getId();
                if (in_array($propertyId, $propertyIds, true)) {
                    $propertiesById[$propertyId] = $property;
                }
            }
        }

        $groupToValueTypeCode = [];

        foreach ($equalPropertyGroups as $groupId => $group) {
            foreach ($group->getPropertyIds() as $propertyId) {
                if ( ! isset($propertiesById[$propertyId])) {
                    throw new \InvalidArgumentException('Property with ID ' . $propertyId . ' not found in any available types');
                }

                $property      = $propertiesById[$propertyId];
                $valueTypeCode = $property->getValueType()->getCode();

                if ( ! isset($groupToValueTypeCode[$groupId])) {
                    $groupToValueTypeCode[$groupId] = $valueTypeCode;
                } else {
                    if ($groupToValueTypeCode[$groupId] !== $valueTypeCode) {
                        throw new \InvalidArgumentException('EqualPropertyGroup must have all properties with single ValueType. Different types \'' . $groupToValueTypeCode[$groupId] . '\' and \'' .
                            $valueTypeCode . '\' found in group ID ' . $groupId);
                    }
                }
            }
        }

        $groupToColumnMapping = [];

        foreach ($groupToValueTypeCode as $groupId => $valueTypeCode) {
            $groupToColumnMapping[$groupId] = $this->em->getEavSettings()->getColumnNameForValueType($valueTypeCode);
        }

        return $groupToColumnMapping;

    }
}