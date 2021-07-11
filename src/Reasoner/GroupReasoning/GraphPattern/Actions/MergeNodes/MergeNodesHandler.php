<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\MergeNodes;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\ActionResult;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge\EntityMerger;
use ANOITCOM\EAVReasonerBundle\Reasoner\Common\GroupsChunker;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions\GraphPatternActionInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GraphGroups;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatchLight;

class MergeNodesHandler implements GraphPatternActionHandlerInterface
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


    public function handle(GraphGroups $graphGroups, GraphPatternActionInterface $action, array $namespaces): ActionResult
    {
        /** @var MergeNodes $action */

        $actionResult = new ActionResult();

        $groups = $graphGroups->getGroups();

        if ( ! count($groups)) {
            return $actionResult;
        }

        $groupsChunks = $this->groupsChunker->chunkGroups($groups, self::BULK_SIZE);

        foreach ($groupsChunks as $groups) {
            $entityIds = [];
            foreach ($groups as $group) {
                /** @var PatternMatchLight $patternMatchLight */
                foreach ($group as $patternMatchLight) {
                    foreach ($action->getNodeAliases() as $nodeAlias) {
                        $entityIds[] = $patternMatchLight->getEntityIdByAlias($nodeAlias);
                    }
                }
            }

            $entities = $this->entityRepository->findBy([ (new FilterCriteria())->whereIn('id', $entityIds) ]);

            /** @var array<string,EAVEntityInterface> $entities */
            $entities = array_combine(array_map(static function (EAVEntityInterface $entity) { return $entity->getId(); }, $entities), $entities);

            foreach ($groups as $group) {
                $groupedByType = [];

                /** @var PatternMatchLight $patternMatchLight */
                foreach ($group as $patternMatchLight) {
                    foreach ($action->getNodeAliases() as $nodeAlias) {
                        $entityId = $patternMatchLight->getEntityIdByAlias($nodeAlias);

                        $entity      = $entities[$entityId];
                        $typeId      = $entity->getType()->getId();
                        $namespaceId = $entity->getNamespace()->getId();

                        $key = $typeId . $namespaceId;
                        if ( ! isset($groupedByType[$key])) {
                            $groupedByType[$key] = [];
                        }

                        // if there are loop in the graph match, a situation may occur when it will be necessary to merge the entity with itself. This will delete it.
                        if ( ! in_array($entity, $groupedByType[$key], true)) {
                            $groupedByType[$key][] = $entity;
                        }
                    }
                }

                foreach ($groupedByType as $typeId => $oneTypeEntities) {
                    if (count($oneTypeEntities) > 1) {
                        $mergeToEntity               = array_shift($oneTypeEntities);
                        $mergedEntitiesActionResult  = $this->merger->mergeEntities($mergeToEntity, $oneTypeEntities, $action->getAfterMergeEntitiesHandler());

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


    public static function getSupportedAction(): string
    {
        return MergeNodes::class;
    }

}