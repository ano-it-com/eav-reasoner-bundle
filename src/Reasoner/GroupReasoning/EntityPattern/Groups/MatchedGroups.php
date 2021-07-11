<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\Groups;

class MatchedGroups
{

    private array $grouped = [];


    public function addToGroup(string $groupId, string $entityId): void
    {
        $this->grouped[$groupId][] = $entityId;
    }


    public function getGroups(): array
    {
        return array_values($this->grouped);
    }

}