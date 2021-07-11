<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\NamespaceEntity\EAVNamespaceInterface;

interface ReasonerInterface
{

    /**
     * @param array<EAVNamespaceInterface> $namespaces
     *
     * @return ActionResult
     */
    public function apply(array $namespaces): ActionResult;
}