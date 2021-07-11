<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\SingleTypeMerge;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\EntityMerger;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\GroupsChunker;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\EntityPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups\MatchedGroups;

class SingleTypeMergeHandler implements EntityPatternActionHandlerInterface
{

    private const BULK_SIZE = 1000;

    private EAVEntityManagerInterface $em;

    private EAVEntityRepositoryInterface $entityRepository;

    private EntityMerger $merger;

    private GroupsChunker $groupsChunker;


    public function __construct(EAVEntityManagerInterface $em, EAVEntityRepositoryInterface $entityRepository, EntityMerger $merger, GroupsChunker $groupsChunker)
    {
        $this->em               = $em;
        $this->entityRepository = $entityRepository;
        $this->merger           = $merger;
        $this->groupsChunker    = $groupsChunker;
    }


    public static function getSupportedAction(): string
    {
        return SingleTypeMerge::class;
    }


    public function handle(MatchedGroups $matchedGroups, EntityPatternActionInterface $action, array $namespaces): ActionResult
    {
        $actionResult = new ActionResult();

        $groups = $matchedGroups->getGroups();

        if ( ! count($groups)) {
            return $actionResult;
        }

        $groupsChunks = $this->groupsChunker->chunkGroups($groups, self::BULK_SIZE);

        foreach ($groupsChunks as $groups) {
            $entityIds = [];
            foreach ($groups as $group) {
                foreach ($group as $entityId) {
                    $entityIds[] = $entityId;
                }
            }

            $entities = $this->entityRepository->findBy([ (new FilterCriteria())->whereIn('id', $entityIds) ]);

            /** @var array<string,EAVEntityInterface> $entities */
            $entities = array_combine(array_map(static function (EAVEntityInterface $entity) { return $entity->getId(); }, $entities), $entities);

            foreach ($groups as $group) {
                $groupedByType = [];
                foreach ($group as $entityId) {
                    $entity      = $entities[$entityId];
                    $typeId      = $entity->getType()->getId();
                    $namespaceId = $entity->getNamespace()->getId();

                    $groupedByType[$typeId . $namespaceId][] = $entity;
                }

                foreach ($groupedByType as $typeId => $oneTypeEntities) {
                    if (count($oneTypeEntities) > 1) {
                        $mergeToEntity              = array_shift($oneTypeEntities);
                        $mergedEntitiesActionResult = $this->merger->mergeEntities($mergeToEntity, $oneTypeEntities, $action->getAfterMergeEntitiesHandler());

                        $relationsToMerge = $this->merger->getRelationsToMerge($mergeToEntity, $oneTypeEntities);
                        $mergedRelationsActionResult = $this->merger->mergeRelations($mergeToEntity, $oneTypeEntities, $relationsToMerge);

                        $actionResult->union($mergedEntitiesActionResult);
                        $actionResult->union($mergedRelationsActionResult);
                    } else {
                        $actionResult->incEntitySkipped(count($oneTypeEntities));
                    }
                }
            }

        }

        if ( ! $actionResult->isEmpty()) {
            $this->em->flush();
            if (isset($entities)) {
                $this->em->forget(...array_values($entities));
                unset($entities);
            }

            if (isset($relationsToMerge)) {
                foreach ($relationsToMerge as $relation) {
                    $this->em->forget($relation->getTo());
                    $this->em->forget($relation->getFrom());
                    $this->em->forget($relation);
                }
                unset($entities);
            }
        }

        return $actionResult;
    }

}