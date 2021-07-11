<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;

class PatternMatch
{

    /**
     * @var array<string, EAVEntityInterface>
     */
    private array $nodeAliasToEntity;


    /**
     * PatternMatch constructor.
     *
     * @param array<string, EAVEntityInterface> $nodeAliasToEntity
     */
    public function __construct(array $nodeAliasToEntity)
    {
        $this->nodeAliasToEntity = $nodeAliasToEntity;
    }


    public function getEntityByAlias(string $alias): EAVEntityInterface
    {
        if ( ! isset($this->nodeAliasToEntity[$alias])) {
            throw new \InvalidArgumentException('Entity or Relation with alias ' . $alias . ' not found in PatternMatch');
        }

        return $this->nodeAliasToEntity[$alias];
    }


    public function toPatternMatchLight(): PatternMatchLight
    {
        $nodeAliasToId = [];
        foreach ($this->nodeAliasToEntity as $alias => $entity) {
            $nodeAliasToId[$alias] = $entity->getId();
        }

        return new PatternMatchLight($nodeAliasToId);
    }
}