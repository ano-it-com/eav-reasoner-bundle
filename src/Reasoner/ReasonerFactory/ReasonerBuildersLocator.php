<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerFactory;

use ANOITCOM\EAVReasonerBundle\Reasoner\ReasonerRuleInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ReasonerBuildersLocator
{

    private ServiceLocator $locator;


    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }


    public function get(ReasonerRuleInterface $rule): ReasonerBuilderInterface
    {
        /** @var ReasonerBuilderInterface $service */
        foreach ($this->locator->getProvidedServices() as $service) {
            if ($service::supports($rule)) {
                return $this->locator->get($service);
            }
        }

        throw new \InvalidArgumentException('ReasonerBuilder not found for reasoner rule ' . get_class($rule));
    }
}