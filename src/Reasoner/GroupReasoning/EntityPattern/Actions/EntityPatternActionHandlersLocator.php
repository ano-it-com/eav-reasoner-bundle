<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Actions;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class EntityPatternActionHandlersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(EntityPatternActionInterface $pattern): EntityPatternActionHandlerInterface
    {
        $class = get_class($pattern);
        try {
            return $this->locator->get($class);
        } catch (NotFoundExceptionInterface $e) {
            throw new \InvalidArgumentException('EntityPatternActionHandler not found for action class ' . $class);
        }
    }
}