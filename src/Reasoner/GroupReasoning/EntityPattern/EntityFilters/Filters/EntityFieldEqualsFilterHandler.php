<?php

namespace ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\Filters;

use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterHandlerInterface;
use ANOITCOM\EAVReasonerBundle\Reasoner\GroupReasoning\EntityPattern\EntityFilters\EntityFilterInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EntityFieldEqualsFilterHandler implements EntityFilterHandlerInterface
{

    public static function getSupportedFilter(): string
    {
        return EntityFieldEqualsFilter::class;
    }


    public function apply(EntityFilterInterface $filter, QueryBuilder $qb, array $tableAliases, int $conditionIndex): void
    {
        /** @var EntityFieldEqualsFilter $filter */

        $field  = $filter->getField();
        $values = $filter->getValues();

        $paramName = $field . '_' . $conditionIndex;
        foreach ($tableAliases as $tableAlias) {
            $qb->andWhere($tableAlias . '.' . $field . ' in (:' . $paramName . ')');
        }

        $qb->setParameter($paramName, $values, Connection::PARAM_STR_ARRAY);
    }
}