<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\Common;

class GroupsChunker
{

    public function chunkGroups(array $groups, int $chunkSize): \Generator
    {
        $groupsChunk = [];
        $inChunk     = 0;
        foreach ($groups as $groupNum => $group) {
            if ($inChunk === 0) {
                $groupsChunk[] = $group;
                $inChunk       = count($group);
                continue;
            }

            if ($inChunk + count($group) > $chunkSize) {
                yield $groupsChunk;
                $groupsChunk = [];
                $inChunk     = 0;
            }

            $groupsChunk[] = $group;
            $inChunk       += count($group);
        }
        
        if (count($groupsChunk)) {
            yield $groupsChunk;
        }
    }
}