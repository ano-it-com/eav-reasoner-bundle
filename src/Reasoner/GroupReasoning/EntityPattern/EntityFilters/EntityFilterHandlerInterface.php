<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters;

use Doctrine\DBAL\Query\QueryBuilder;

interface EntityFilterHandlerInterface
{

    public static function getSupportedFilter(): string;


    /**
     * @param EntityFilterInterface $filter
     * @param QueryBuilder          $qb
     * @param array<string>         $tableAliases
     * @param int                   $conditionIndex
     */
    public function apply(EntityFilterInterface $filter, QueryBuilder $qb, array $tableAliases, int $conditionIndex): void;
}