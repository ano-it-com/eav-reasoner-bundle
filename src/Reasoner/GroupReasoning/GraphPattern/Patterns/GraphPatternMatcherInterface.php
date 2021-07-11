<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatch;

interface GraphPatternMatcherInterface
{

    public static function getSupportedPattern(): string;


    /**
     * @param GraphPatternInterface        $pattern
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return \Generator<\ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\PatternMatch\PatternMatch>
     */
    public function getPatternMatches(GraphPatternInterface $pattern, array $namespaces = []): \Generator;
}