<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;

/**
 * Store only ID for each entity to save memory
 */
class PatternMatchLight
{

    /**
     * @var array<string, string>
     */
    private array $nodeAliasToId;


    /**
     * PatternMatch constructor.
     *
     * @param array<string, EAVEntityInterface> $nodeAliasToId
     */
    public function __construct(array $nodeAliasToId)
    {
        $this->nodeAliasToId = $nodeAliasToId;
    }


    public function getEntityIdByAlias(string $alias): string
    {
        if ( ! isset($this->nodeAliasToId[$alias])) {
            throw new \InvalidArgumentException('Entity or Relation with alias ' . $alias . ' not found in PatternMatchLight');
        }

        return $this->nodeAliasToId[$alias];
    }
}