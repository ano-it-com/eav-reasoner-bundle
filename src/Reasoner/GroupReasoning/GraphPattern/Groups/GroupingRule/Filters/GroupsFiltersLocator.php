<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\GraphPattern\Groups\GroupingRule\Filters;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class GroupsFiltersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(GroupsFilterInterface $pattern): GroupsFilterHandlerInterface
    {
        $class = get_class($pattern);
        try {
            return $this->locator->get($class);
        } catch (NotFoundExceptionInterface $e) {
            throw new \InvalidArgumentException('GroupsFilterHandlerInterface not found for groups filter class ' . $class);
        }
    }
}