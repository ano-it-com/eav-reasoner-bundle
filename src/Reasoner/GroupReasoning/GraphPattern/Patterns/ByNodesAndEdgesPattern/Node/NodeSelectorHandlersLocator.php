<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Patterns\ByNodesAndEdgesPattern\Node;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class NodeSelectorHandlersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(NodeSelectorInterface $pattern): NodeSelectorHandlerInterface
    {
        $class = get_class($pattern);
        try {
            return $this->locator->get($class);
        } catch (NotFoundExceptionInterface $e) {
            throw new \InvalidArgumentException('NodeSelectorHandlerInterface not found for selector class ' . $class);
        }
    }
}