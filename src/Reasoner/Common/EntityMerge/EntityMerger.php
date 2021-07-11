<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;

class EntityMerger
{

    private EAVEntityRelationRepositoryInterface $relationRepository;

    private EAVEntityManagerInterface $em;


    public function __construct(EAVEntityManagerInterface $em, EAVEntityRelationRepositoryInterface $relationRepository)
    {
        $this->relationRepository = $relationRepository;
        $this->em                 = $em;
    }


    /**
     * @param EAVEntityInterface                      $mergeToEntity
     * @param array<EAVEntityInterface>               $mergedEntities
     * @param AfterMergeEntitiesHandlerInterface|null $afterMergeEntitiesHandler
     *
     * @return ActionResult
     */
    public function mergeEntities(EAVEntityInterface $mergeToEntity, array $mergedEntities, ?AfterMergeEntitiesHandlerInterface $afterMergeEntitiesHandler = null): ActionResult
    {
        $mergeToEntityTypeId = $mergeToEntity->getType()->getId();

        $mergeToEntityValues = [];
        foreach ($mergeToEntity->getValues() as $propertyValue) {
            $propertyTypeId = $propertyValue->getTypePropertyId();
            if ($propertyValue->getValue() === null) {
                continue;
            }
            $value = $propertyValue->getValueAsString();

            $mergeToEntityValues[$propertyTypeId][$value] = true;
        }

        foreach ($mergedEntities as $mergedEntity) {
            if ($mergeToEntity === $mergedEntity) {
                continue;
            }
            $mergedEntityTypeId = $mergedEntity->getType()->getId();
            if ($mergedEntityTypeId !== $mergeToEntityTypeId) {
                throw new \InvalidArgumentException('Cannot merge entities with different types: ' . $mergeToEntityTypeId . ' and ' . $mergedEntityTypeId);
            }

            foreach ($mergedEntity->getValues() as $propertyValue) {
                if ($propertyValue->getValue() === null) {
                    continue;
                }
                $propertyTypeId = $propertyValue->getTypePropertyId();
                $value          = $propertyValue->getValueAsString();

                if ( ! isset($mergeToEntityValues[$propertyTypeId][$value])) {
                    $mergeToEntity->addPropertyValueByPropertyTypeId($propertyTypeId, $propertyValue->getValue());
                    $mergeToEntityValues[$propertyTypeId][$value] = true;
                }
            }

            $this->mergeMeta($mergeToEntity, $mergedEntity);

            if ($afterMergeEntitiesHandler) {
                $afterMergeEntitiesHandler->handle($mergeToEntity, $mergedEntity);
            }

            $this->em->remove($mergedEntity);
        }

        $actionResult = new ActionResult();

        $actionResult->incEntityMerged(count($mergedEntities));
        $actionResult->incEntityUpdated();

        return $actionResult;
    }


    /**
     * @param EAVEntityInterface        $mergeToEntity
     * @param array<EAVEntityInterface> $mergedEntities
     *
     * @return array<EAVEntityRelationInterface>
     */
    public function getRelationsToMerge(EAVEntityInterface $mergeToEntity, array $mergedEntities): array
    {
        $mergedEntities[]  = $mergeToEntity;
        $mergedEntitiesIds = array_map(static function (EAVEntityInterface $entity) { return $entity->getId(); }, $mergedEntities);

        return $this->relationRepository->findBy([ (new FilterCriteria())->orWhereIn('from_id', $mergedEntitiesIds)->orWhereIn('to_id', $mergedEntitiesIds) ]);
    }


    /**
     * @param EAVEntityInterface                $mergeToEntity
     * @param array<EAVEntityInterface>         $mergedEntities
     * @param array<EAVEntityRelationInterface> $relationsToMerge
     *
     * @return ActionResult
     */
    public function mergeRelations(EAVEntityInterface $mergeToEntity, array $mergedEntities, array $relationsToMerge): ActionResult
    {
        $mergedEntitiesIdsMap = array_map(static function (EAVEntityInterface $entity) { return $entity->getId(); }, $mergedEntities);
        $mergedEntitiesIdsMap = array_fill_keys($mergedEntitiesIdsMap, true);

        $groupedDuplicates = [];

        foreach ($relationsToMerge as $relation) {
            if (isset($mergedEntitiesIdsMap[$relation->getFrom()->getId()])) {
                $relation->setFrom($mergeToEntity);
            }
            if (isset($mergedEntitiesIdsMap[$relation->getTo()->getId()])) {
                $relation->setTo($mergeToEntity);
            }

            $uniqueKey = $this->makeUniqueRelationKey($relation);

            if ( ! isset($groupedDuplicates[$uniqueKey])) {
                $groupedDuplicates[$uniqueKey] = [];
            }

            $groupedDuplicates[$uniqueKey][] = $relation;
        }

        $removedCount = 0;
        foreach ($groupedDuplicates as $duplicatesGroup) {
            foreach ($duplicatesGroup as $i => $relation) {
                if ($i === 0) {
                    continue;
                }

                $this->em->remove($relation);
                $removedCount++;
            }
        }

        $actionResult = new ActionResult();
        $actionResult->incRelationMerged(count($relationsToMerge));
        $actionResult->incRelationDeleted($removedCount);

        return $actionResult;
    }


    private function makeUniqueRelationKey(EAVEntityRelationInterface $relation): string
    {
        return implode('_', [ $relation->getFrom()->getId(), $relation->getTo()->getId(), $relation->getType()->getId(), $relation->getNamespace()->getId() ]);
    }


    private function mergeMeta(EAVEntityInterface $to, EAVEntityInterface $from): void
    {
        $toMeta   = $to->getMeta();
        $fromMeta = $from->getMeta();

        $toMeta = $toMeta->merge($fromMeta);
        $to->setMeta($toMeta);
    }
}