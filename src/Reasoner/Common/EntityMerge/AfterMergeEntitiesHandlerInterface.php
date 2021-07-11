<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\Common\EntityMerge;

use ANOITCOM\EAVBundle\EAV\ORM\Entity\Entity\EAVEntityInterface;

interface AfterMergeEntitiesHandlerInterface
{

    public function handle(EAVEntityInterface $to, EAVEntityInterface $from): void;
}