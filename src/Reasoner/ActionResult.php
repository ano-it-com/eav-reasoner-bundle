<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner;

class ActionResult
{

    private int $entityMerged = 0;

    private int $entitySkipped = 0;

    private int $entityUpdated = 0;

    private int $entityDeleted = 0;

    private int $entityCreated = 0;

    private int $relationMerged = 0;

    private int $relationSkipped = 0;

    private int $relationUpdated = 0;

    private int $relationDeleted = 0;

    private int $relationCreated = 0;


    public function isEmpty(): bool
    {
        return ! (
            $this->entityMerged ||
            $this->entitySkipped ||
            $this->entityUpdated ||
            $this->entityDeleted ||
            $this->entityCreated ||
            $this->relationMerged ||
            $this->relationSkipped ||
            $this->relationUpdated ||
            $this->relationDeleted ||
            $this->relationCreated
        );
    }


    public function union(ActionResult $actionResult): void
    {
        $this->entityMerged    += $actionResult->entityMerged;
        $this->entitySkipped   += $actionResult->entitySkipped;
        $this->entityUpdated   += $actionResult->entityUpdated;
        $this->entityDeleted   += $actionResult->entityDeleted;
        $this->entityCreated   += $actionResult->entityCreated;
        $this->relationMerged  += $actionResult->relationMerged;
        $this->relationSkipped += $actionResult->relationSkipped;
        $this->relationUpdated += $actionResult->relationUpdated;
        $this->relationDeleted += $actionResult->relationDeleted;
        $this->relationCreated += $actionResult->relationCreated;
    }


    public function incEntityMerged(int $count = 1): void
    {
        $this->entityMerged += $count;
    }


    public function incEntitySkipped(int $count = 1): void
    {
        $this->entitySkipped += $count;
    }


    public function incEntityUpdated(int $count = 1): void
    {
        $this->entityUpdated += $count;
    }


    public function incEntityDeleted(int $count = 1): void
    {
        $this->entityDeleted += $count;
    }


    public function incEntityCreated(int $count = 1): void
    {
        $this->entityCreated += $count;
    }


    public function incRelationMerged(int $count = 1): void
    {
        $this->relationMerged += $count;
    }


    public function incRelationSkipped(int $count = 1): void
    {
        $this->relationSkipped += $count;
    }


    public function incRelationUpdated(int $count = 1): void
    {
        $this->relationUpdated += $count;
    }


    public function incRelationDeleted(int $count = 1): void
    {
        $this->relationDeleted += $count;
    }


    public function incRelationCreated(int $count = 1): void
    {
        $this->relationCreated += $count;
    }

}