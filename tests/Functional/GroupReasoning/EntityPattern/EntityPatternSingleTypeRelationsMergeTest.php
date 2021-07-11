<?php

namespace ANOITCOM\EAVReasonerBundle\Tests\Functional\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\SingleTypeMerge\SingleTypeMerge;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common\EqualPropertyGroup;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\Filters\EntityFieldEqualsFilter;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityPatternRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualPropertiesPattern\EntityEqualPropertiesPattern;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory\ReasonerFactory;
use ANOITCOM\EAVReasonerBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVReasonerBundle\Tests\TestCases\BundleWithPostgresTestCase;

class EntityPatternSingleTypeRelationsMergeTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    private $em;

    /** @var EAVEntityRepository */
    private $entityRepository;

    /** @var EAVEntityRelationRepository */
    private $relationRepository;

    /** @var ReasonerFactory */
    private $reasonerFactory;

    /** @var EntitiesFactory */
    private $entitiesFactory;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }


    protected function setUp(): void
    {
        parent::setUp();
        self::createDbAndMigrations();

        $this->em                 = self::$container->get(EAVEntityManager::class);
        $this->reasonerFactory    = self::$container->get(ReasonerFactory::class);
        $this->entityRepository   = self::$container->get(EAVEntityRepository::class);
        $this->relationRepository = self::$container->get(EAVEntityRelationRepository::class);
        $this->entitiesFactory    = new EntitiesFactory($this->em);
    }


    public function testMergeRelations(): void
    {
        // prepare
        $namespace    = $this->entitiesFactory->createNamespace();
        $type         = $this->entitiesFactory->createType($namespace, 2);
        $relationType = $this->entitiesFactory->createRelationType($namespace);

        $propValue1ToMerge = 'value1';
        $propValue2ToMerge = 'value2';

        $entity1 = $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge);
        $entity2 = $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge);
        $entity3 = $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge);

        $propValue1NotToMerge = 'valueNot1';
        $propValue2NotToMerge = 'valueNot2';

        $notMergedEntity = $this->entitiesFactory->createEntity($namespace, $type, $propValue1NotToMerge, $propValue2NotToMerge);

        $this->entitiesFactory->createRelation($namespace, $relationType, $entity1, $notMergedEntity);
        $this->entitiesFactory->createRelation($namespace, $relationType, $entity2, $notMergedEntity);
        $this->entitiesFactory->createRelation($namespace, $relationType, $entity3, $notMergedEntity);

        $this->entitiesFactory->createRelation($namespace, $relationType, $notMergedEntity, $entity1);
        $this->entitiesFactory->createRelation($namespace, $relationType, $notMergedEntity, $entity2);
        $this->entitiesFactory->createRelation($namespace, $relationType, $notMergedEntity, $entity3);

        $this->em->flush();
        $this->em->clear();

        $equalPropertyGroups = [];
        foreach ($type->getProperties() as $property) {
            if (in_array($property->getAlias(), [ 'property1', 'property2' ], true)) {
                $equalPropertyGroups[] = new EqualPropertyGroup([ $property->getId() ]);
            }
        }

        // merge rule
        $pattern = new EntityEqualPropertiesPattern($equalPropertyGroups, [ new EntityFieldEqualsFilter('type_id', $type->getId()) ]);

        $action = new SingleTypeMerge();

        $rule = new EntityPatternRule($pattern, $action);

        $reasoner = $this->reasonerFactory->build($rule);

        $reasoner->apply([ $namespace ]);

        // asserts
        $allEntities = $this->entityRepository->findBy([ (new FilterCriteria())->where('namespace_id', '=', $namespace->getId()) ]);

        $allRelations = $this->relationRepository->findBy([]);

        self::assertCount(2, $allEntities);
        self::assertCount(2, $allRelations);

    }

}