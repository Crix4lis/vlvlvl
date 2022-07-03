<?php

namespace App\ConfGenerator;

use App\Utils\IsAssocArrayTrait;
use JetBrains\PhpStorm\ArrayShape;

class ConfigGenerator implements ConfigGeneratorInterface
{
    use IsAssocArrayTrait;

    public function __construct(
        private readonly ConfigValidatorInterface $validator,
    ) {}

    public function generate(array $base, array $params): array
    {
        $this->validator->validate($params, $base);
        $mergedData = $this->mergeData($base, $params);
        $plainData = $this->splitObject($mergedData);
        $allCombinations = $this->generateAllCombinations($plainData);

        return $this->reconstructObjects($allCombinations);
    }

    private function reconstructObjects(array $toRestructure): array
    {
        $return = [];
        foreach ($toRestructure as $combination) {
            $sortedByLevel = $this->sortByNestLevel($combination);
            $deepestLevel = $this->getDeepestLevel($sortedByLevel);

            $return[] = $this->restoreStructure($combination, $deepestLevel);
        }

        return $return;
    }

    private function getDeepestLevel(array $sortedByLevel): int
    {
        return end($sortedByLevel)['nested_level'];
    }

    private function sortByNestLevel(array $combination): array
    {
        uasort($combination, function ($val1, $val2) {
            return $val1['nested_level'] < $val2['nested_level'] ? -1 : 1;
        });

        return $combination;
    }

    private function generateAllCombinations(array $data): array
    {
        $allKeysToCreateCombinationsFrom = array_unique(array_map(fn ($val) => $val['key_name'], $data));
        $keyCountedValues = $this->countPossobileValuesForKey($allKeysToCreateCombinationsFrom, $data);
        asort($keyCountedValues);
        $keyCountedValues = array_reverse($keyCountedValues);

        $generatedData = [];

        foreach ($keyCountedValues as $keyName => $valuesCount) {
            $startWithCurrKeyIndex = $valuesCount - 1;
            $startWithRestIndex = $startWithCurrKeyIndex;

            while ($startWithRestIndex >= 0) {
                $generatedData = $this->generateCombinationForGivenKey(
                    $keyName,
                    $startWithCurrKeyIndex,
                    $startWithRestIndex,
                    $keyCountedValues,
                    $data,
                    $generatedData,
                );
                $startWithRestIndex--;
            }
        }

        return $this->arrayUnique($generatedData);
    }

    private function generateCombinationForGivenKey(
        string $currentKeyNameToChangeValuesFor,
        int $currentKeyValueIndex,
        int $restKeyValueIndex,
        array $keysAndMaxValues,
        array $data,
        array $generated = []
    ): array
    {
        $singleCombination = [];

        foreach ($keysAndMaxValues as $keyName => $countedValues) {
            if ($keyName === $currentKeyNameToChangeValuesFor) {
                $valueIndex = min($countedValues - 1, $currentKeyValueIndex);
            } else {
                $valueIndex = min($countedValues - 1, $restKeyValueIndex);
            }
            $singleCombination[$keyName] = $this->findValueForKeyAndValueIndex($keyName, $valueIndex, $data);
        }

        $generated[] = $singleCombination;

        if ($currentKeyValueIndex !== 0) {
            $generated = $this->generateCombinationForGivenKey(
                $currentKeyNameToChangeValuesFor,
                $currentKeyValueIndex - 1,
                $restKeyValueIndex,
                $keysAndMaxValues,
                $data,
                $generated,
            );
        }

        return $generated;
    }

    private function findValueForKeyAndValueIndex(
        string $keyName,
        int $valueIndex,
        array $fromData
    ): array
    {
        foreach ($fromData as $item) {
            if ($item['key_name'] === $keyName &&
                $item['key_value_index'] === $valueIndex
            ) {
                return $item;
            }

            //if 0 values, only nested object
            if ($item['key_name'] === $keyName &&
                $item['key_value_index'] === null
            ) {
                return $item;
            }
        }

        throw new \RuntimeException('Wrong Index given!');
    }

    private function countPossobileValuesForKey(array $keyNames, array $data): array
    {
        $counted = [];

        foreach ($keyNames as $kName) {
            $count = 0;
            array_walk($data, function (array $item) use (&$count, $kName) {
                if ($item['key_name'] === $kName) {
                    $count++;
                }
            });
            $counted[$kName] = $count;
        }

        return $counted;
    }

    #[ArrayShape([[
        'key_name' => 'string',
        'key_value' => 'mixed', //array of elements or element
        'key_value_index' => 'int|null', // if int - one of possible primitive vales, if null - no value - contains nested object
        'nested_level' => 'int',
        'key_route' => 'string|null',
        'key_children' => 'string|array|null',
    ]])]
    private function splitObject(
        array $object,
        array $split = [],
        int $nestedLevel = 0,
        string $parentKeyRoute = null,
    ): array
    {
        $keyChildren = null;
        $nestedObjects = [];

        foreach ($object as $key => $value) {
            if ($this->hasNestedObject($value)) {
                $keyChildren = array_keys($value);
                $nestedObjects[$key] = $value;
            }

            if ($this->isArrayOfPossibleValues($value)) {
                foreach ($value as $k => $singleVal) {
                    $split[] = [
                        'key_name' => $key,
                        'key_value' => $singleVal,
                        'key_value_index' => $k,
                        'nested_level' => $nestedLevel,
                        'key_route' => $parentKeyRoute,
                        'key_children' => $keyChildren,
                    ];
                }
                continue;
            }

            if ($this->isTheyOnlyPossibleValue($value)) {
                $split[] = [
                    'key_name' => $key,
                    'key_value' => $value,
                    'key_value_index' => 0,
                    'nested_level' => $nestedLevel,
                    'key_route' => $parentKeyRoute,
                    'key_children' => $keyChildren,
                ];
                continue;
            }

            //no values, contains nested objects
            $split[] = [
                'key_name' => $key,
                'key_value' => null,
                'key_value_index' => null,
                'nested_level' => $nestedLevel,
                'key_route' => $parentKeyRoute,
                'key_children' => $keyChildren,
            ];
        }

        foreach ($nestedObjects as $parentKey => $nestedObject) {
            $split = $this->splitObject(
                $nestedObject,
                $split,
                $nestedLevel + 1,
                $parentKeyRoute === null ? $parentKey : $parentKeyRoute.'.'.$parentKey,
            );
        }

        return $split;
    }

    private function isArrayOfPossibleValues(mixed $value): bool
    {
        return is_array($value) && $this->isAssocArray($value) === false;
    }

    private function isTheyOnlyPossibleValue(mixed $value): bool
    {
        return $this->isAssocArray($value) === false;
    }

    private function hasNestedObject(mixed $value): bool
    {
        return $this->isAssocArray($value);
    }

    private function mergeData(array $base, array $params): array
    {
        $merged = [];
        foreach ($base as $baseKey => $baseValue) {
            // if value in definition is json object, go recursive
            if ($this->isAssocArray($baseValue)) {
                $merged[$baseKey] = $this->mergeData(
                    $baseValue,
                    array_key_exists($baseKey, $params) ? $params[$baseKey] : $baseValue
                );
                continue;
            }

            // if value in definition is primitive, put possible values
            $merged[$baseKey] = array_key_exists($baseKey, $params) ?
                $params[$baseKey] :
                $baseValue;
        }

        return $merged;
    }

    private function restoreStructure(
        array $sortedToRestore,
        int $deepestLevel,
        int $currentLevelToRestore = 0,
        array $restored = [],
    ): array
    {
        if ($currentLevelToRestore > $deepestLevel) {
            return $restored;
        }

        foreach ($sortedToRestore as $keyName => $keyData) {
            if ($keyData['nested_level'] !== $currentLevelToRestore) {
                continue;
            }

            if ($keyData['key_route'] === null) {
                $restored[$keyName] = $keyData['key_value'];
                continue;
            }

            $keyRoute = explode('.', $keyData['key_route']);

            $restored = $this->restoreNested(
                $keyData,
                $keyRoute,
                $restored,
                $restored,
            );
        }

        return $this->restoreStructure(
            $sortedToRestore,
            $deepestLevel,
            $currentLevelToRestore + 1,
            $restored
        );
    }

    private function restoreNested(
        array $keyData,
        array $keyRoute,
        array $branch,
        array $originalData,
        int $currentLevel = 0,
        string $prevIndex = null,
    )
    {
        if (($currentLevel + 1) === $keyData['nested_level']) {
            if ($prevIndex === null) {
                $existingToMerge = $branch[$keyRoute[$currentLevel]];
                if ($existingToMerge === null) {
                    $branch[$keyRoute[$currentLevel]] = [$keyData['key_name'] => $keyData['key_value']];
                } else {
                    $branch[$keyRoute[$currentLevel]] = array_merge(
                        [$keyData['key_name'] => $keyData['key_value']],
                        $existingToMerge,
                    );
                }
            } else {
                array_walk_recursive($branch, function (&$value, $key) use ($prevIndex, $keyData) {
                    if ($prevIndex === $key) {
                        if ($value === null) {
                            $value = [$keyData['key_name'] => $keyData['key_value']];
                        } else {
                            $value = array_merge(
                                [$keyData['key_name'] => $keyData['key_value']],
                                $value,
                            );
                        }
                    }
                });
            }

            return $branch;
        }

        $nextLevel = $currentLevel + 1;
        return $this->restoreNested(
            $keyData,
            $keyRoute,
            $branch,
            $originalData,
            $nextLevel,
            $keyRoute[$nextLevel],
        );
    }
}
