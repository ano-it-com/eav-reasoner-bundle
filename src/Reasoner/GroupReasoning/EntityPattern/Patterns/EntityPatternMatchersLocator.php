<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Patterns;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class EntityPatternMatchersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(EntityPatternInterface $pattern): EntityPatternMatcherInterface
    {
        $class = get_class($pattern);
        try {
            return $this->locator->get($class);
        } catch (NotFoundExceptionInterface $e) {
            throw new \InvalidArgumentException('EntityPatternMatcher not found for pattern class ' . $class);
        }
    }
}