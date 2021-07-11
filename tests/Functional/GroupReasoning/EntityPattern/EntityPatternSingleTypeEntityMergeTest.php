<?php

namespace ANOITCOM\EAVReasonerBundle\Tests\Functional\Entity;

use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\CommonFilters\FilterCriteria\FilterCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Criteria\Filter\EntityFilters\EntityPropertyValue\EntityPropertyValueCriteria;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions\SingleTypeMerge\SingleTypeMerge;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Common\EqualPropertyGroup;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\Filters\EntityFieldEqualsFilter;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityPatternRule;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns\EntityEqualPropertiesPattern\EntityEqualPropertiesPattern;
use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory\ReasonerFactory;
use ANOITCOM\EAVReasonerBundle\Tests\Functional\Helpers\EntitiesFactory;
use ANOITCOM\EAVReasonerBundle\Tests\TestCases\BundleWithPostgresTestCase;

class EntityPatternSingleTypeEntityMergeTest extends BundleWithPostgresTestCase
{

    /** @var EAVEntityManager */
    private $em;

    /** @var EAVEntityRepositoryInterface */
    private $entityRepository;

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

        $this->em               = self::$container->get(EAVEntityManager::class);
        $this->reasonerFactory  = self::$container->get(ReasonerFactory::class);
        $this->entityRepository = self::$container->get(EAVEntityRepository::class);
        $this->entitiesFactory  = new EntitiesFactory($this->em);
    }


    public function testMergeOneNamespaceOneTypeEntities(): void
    {
        // prepare
        $namespace = $this->entitiesFactory->createNamespace();
        $type      = $this->entitiesFactory->createType($namespace, 3);

        $propValue1ToMerge = 'value1';
        $propValue2ToMerge = 'value2';
        $propValue3ToMerge = 'value3';

        $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge);
        $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge);
        $this->entitiesFactory->createEntity($namespace, $type, $propValue1ToMerge, $propValue2ToMerge, $propValue3ToMerge);

        $propValue1NotToMerge = 'valueNot1';
        $propValue2NotToMerge = 'valueNot2';

        $notMergedEntity = $this->entitiesFactory->createEntity($namespace, $type, $propValue1NotToMerge, $propValue2NotToMerge);

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
        $allEntities     = $this->entityRepository->findBy([ (new FilterCriteria())->where('namespace_id', '=', $namespace->getId()) ]);
        $mergedEntities  = $this->entityRepository->findBy([
            (new FilterCriteria())->where('namespace_id', '=', $namespace->getId()),
            (new EntityPropertyValueCriteria())
                ->where($type->getPropertyByAlias('property1')->getId(), '=', $propValue1ToMerge)
                ->where($type->getPropertyByAlias('property2')->getId(), '=', $propValue2ToMerge)
        ]);
        $notMergedEntity = $this->entityRepository->find($notMergedEntity->getId());

        self::assertCount(2, $allEntities);
        self::assertNotNull($notMergedEntity);
        self::assertCount(1, $mergedEntities);

        /** @var EAVEntity $mergedEntity */
        $mergedEntity = $mergedEntities[0];

        $values = $mergedEntity->getValues();
        self::assertCount(3, $values);
        $found = 0;
        foreach ($values as $value) {
            if ($value->getTypePropertyId() === $type->getPropertyByAlias('property1')->getId()) {
                $found++;
                self::assertEquals($propValue1ToMerge, $value->getValue());
            }
            if ($value->getTypePropertyId() === $type->getPropertyByAlias('property2')->getId()) {
                $found++;
                self::assertEquals($propValue2ToMerge, $value->getValue());
            }
            if ($value->getTypePropertyId() === $type->getPropertyByAlias('property3')->getId()) {
                $found++;
                self::assertEquals($propValue3ToMerge, $value->getValue());
            }
        }

        self::assertEquals(3, $found);
    }


    public function testDoesntMergeDifferentNamespaceEntities(): void
    {
        // prepare
        $namespace1 = $this->entitiesFactory->createNamespace();
        $namespace2 = $this->entitiesFactory->createNamespace();
        $type       = $this->entitiesFactory->createType($namespace1, 2);

        $propValue1ToMerge = 'value1';
        $propValue2ToMerge = 'value2';

        $this->entitiesFactory->createEntity($namespace1, $type, $propValue1ToMerge, $propValue2ToMerge);
        $this->entitiesFactory->createEntity($namespace2, $type, $propValue1ToMerge, $propValue2ToMerge);

        $this->em->flush();
        $this->em->clear();

        $equalPropertyGroups = [];
        foreach ($type->getProperties() as $property) {
            $equalPropertyGroups[] = new EqualPropertyGroup([ $property->getId() ]);
        }

        // merge rule
        $pattern = new EntityEqualPropertiesPattern($equalPropertyGroups, [ new EntityFieldEqualsFilter('type_id', $type->getId()) ]);

        $action = new SingleTypeMerge();

        $rule = new EntityPatternRule($pattern, $action);

        $reasoner = $this->reasonerFactory->build($rule);

        $reasoner->apply([ $namespace1, $namespace2 ]);

        // asserts
        $allEntities = $this->entityRepository->findBy([ (new FilterCriteria())->whereIn('namespace_id', [ $namespace1->getId(), $namespace2->getId() ]) ]);

        self::assertCount(2, $allEntities);
    }


    public function testDoesntMergeDifferentTypesEntities(): void
    {
        // prepare
        $namespace = $this->entitiesFactory->createNamespace();
        $type1     = $this->entitiesFactory->createType($namespace, 2);
        $type2     = $this->entitiesFactory->createType($namespace, 2);

        $propValue1ToMerge = 'value1';
        $propValue2ToMerge = 'value2';

        $this->entitiesFactory->createEntity($namespace, $type1, $propValue1ToMerge, $propValue2ToMerge);
        $this->entitiesFactory->createEntity($namespace, $type2, $propValue1ToMerge, $propValue2ToMerge);

        $this->em->flush();
        $this->em->clear();

        $equalPropertyGroups = [];
        foreach ($type1->getProperties() as $property1) {
            $property2             = $type2->getPropertyByAlias($property1->getAlias());
            $equalPropertyGroups[] = new EqualPropertyGroup([ $property1->getId(), $property2->getId() ]);
        }

        // merge rule
        $pattern = new EntityEqualPropertiesPattern($equalPropertyGroups, [ new EntityFieldEqualsFilter('type_id', [ $type1->getId(), $type2->getId() ]) ]);

        $action = new SingleTypeMerge();

        $rule = new EntityPatternRule($pattern, $action);

        $reasoner = $this->reasonerFactory->build($rule);

        $reasoner->apply([ $namespace ]);

        // asserts
        $allEntities = $this->entityRepository->findBy([ (new FilterCriteria())->where('namespace_id', '=', $namespace->getId()) ]);

        self::assertCount(2, $allEntities);
    }
}