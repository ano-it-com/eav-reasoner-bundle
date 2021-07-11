# EAV Reasoner

Библиотека обработки данных для EAV-bundle

## Основные возможности

* Слияние (merge) сущностей по правилам
* Логические выводы на графе

## Примеры

### Слияние по значениям свойств

```
// группа свойств, значения которых должны быть равны
// в группе свойства имеющие одинаковое смысловое значение, например, в одной группе могут быть различные свойства, обозначающие "Фамилию"
$equalPropertyGroups = [new EqualPropertyGroup([ $fioPropertyType->getId(), $anotherFioPropertyType ]), new EqualPropertyGroup([ $birthdayPropertyType->getId() ])]

// паттерн поиска объектов - по одинаковым значениям свойств, дополнительный фильтр по типу объектов
$pattern = new EntityEqualPropertiesPattern($equalPropertyGroups, [ new EntityFieldEqualsFilter('type_id', $personType->getId()) ]);

// Действие для найденных объектов
$action = new SingleTypeMerge();

$rule = new EntityPatternRule($pattern, $action);

$reasoner = $this->reasonerFactory->build($rule);

// Слияние осуществляется в рамках пространства имен
$reasoner->apply([ $namespace ]);

```

### Слияние по образцу графа

```
// описание образца графа
// Персона -> [Имеет банковский счет] -> Банковский счет (пустая сущность) -> [Привязана карта] -> Карта
$pattern = new ByNodesAndEdgesPattern([
   new NodeByType([ $bankCardType->getId() ], 'card'),
   new NodeByType([ $bankAccountType->getId() ], 'account', [ new EmptyEntity() ]),
   new NodeByType([ $personType->getId() ], 'person'),
], [
   new EdgeByType('card', [ $linkedToBankRelationType->getId() ], 'account'),
   new EdgeByType('account', [ $isBankAccountOfRelationType->getId() ], 'person'),
]);

// правила группировки
// в одну группу для слияния попадают подграфы, у которых одни и те же объекты Карта и Персона
$groupingRules = [
   new GroupingRule('card', [ new SameObject() ]),
   new GroupingRule('person', [ new SameObject() ]),
];

// действие - слияние сущностей Банковский счет
$action = new MergeNodes('account');

$rule = new GraphPatternRule($pattern, $groupingRules, $action);
$reasoner = $reasonerFactory->build($rule);
$reasoner->apply([ $namespace ]);
```