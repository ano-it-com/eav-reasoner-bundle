<?php

namespace ANOITCOM\EAVReasonerBundle\Tests\Functional\Helpers;

use ANOITCOM\EAVBundle\EAV\ORM\DBAL\Types\TextType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntity;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelation;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\EntityRelation\EAVEntityRelationType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespace;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVType;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Entity\Type\EAVTypeProperty;
use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManager;
use Ramsey\Uuid\Uuid;

class EntitiesFactory
{

    private EAVEntityManager $em;


    public function __construct(EAVEntityManager $em)
    {
        $this->em = $em;
    }


    public function createEntity(EAVNamespace $namespace, EAVTypeInterface $type, $propValue1, $propValue2, $propValue3 = null): EAVEntity
    {
        $entity = new EAVEntity(Uuid::uuid4(), $namespace, $type);

        $entity->addPropertyValueByAlias('property1', $propValue1);
        $entity->addPropertyValueByAlias('property2', $propValue2);

        if ($propValue3) {
            $entity->addPropertyValueByAlias('property3', $propValue3);
        }

        $this->em->persist($entity);

        return $entity;
    }


    public function createNamespace(): EAVNamespace
    {
        $namespace = new EAVNamespace(Uuid::uuid4()->toString(), Uuid::uuid4());
        $namespace->setTitle('test namespace');

        $this->em->persist($namespace);

        return $namespace;
    }


    public function createType(EAVNamespace $namespace, int $propertiesCount, int $index = 1): EAVType
    {
        $type = new EAVType(Uuid::uuid4()->toString(), $namespace);
        $type->setAlias('type' . $index);
        $type->setTitle('type' . $index);

        $properties = [];

        for ($i = 1; $i <= $propertiesCount; $i++) {
            $prop = new EAVTypeProperty(Uuid::uuid4()->toString(), $namespace, $type, new TextType());
            $prop->setAlias('property' . $i);
            $prop->setTitle('property' . $i);

            $properties[] = $prop;
        }

        $type->setProperties(array_values($properties));
        $this->em->persist($type);

        return $type;
    }


    public function createRelationType(EAVNamespace $namespace, int $index = 1): EAVEntityRelationType
    {
        $type = new EAVEntityRelationType(Uuid::uuid4()->toString(), $namespace);
        $type->setAlias('relation_type' . $index);
        $type->setTitle('relation_type' . $index);

        $this->em->persist($type);

        return $type;
    }


    public function createRelation(EAVNamespace $namespace, EAVEntityRelationType $relationType, EAVEntity $from, EAVEntity $to): EAVEntityRelation
    {
        $relation = new EAVEntityRelation(Uuid::uuid4(), $namespace, $relationType);
        $relation->setFrom($from);
        $relation->setTo($to);

        $this->em->persist($relation);

        return $relation;
    }
}