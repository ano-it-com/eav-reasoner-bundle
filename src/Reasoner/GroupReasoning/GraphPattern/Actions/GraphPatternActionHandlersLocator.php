<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Actions;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class GraphPatternActionHandlersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(GraphPatternActionInterface $pattern): GraphPatternActionHandlerInterface
    {
        $class = get_class($pattern);
        try {
            return $this->locator->get($class);
        } catch (NotFoundExceptionInterface $e) {
            throw new \InvalidArgumentException('GraphPatternActionHandler not found for action class ' . $class);
        }
    }
}