<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups\MatchedGroups;

interface EntityPatternMatcherInterface
{

    /**
     * @param EntityPatternInterface       $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return MatchedGroups
     */
    public function getMatchedGroups(EntityPatternInterface $pattern, array $namespaces = []): MatchedGroups;


    public static function getSupportedPattern(): string;
}