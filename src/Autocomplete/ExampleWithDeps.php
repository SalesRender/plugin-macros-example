<?php

namespace SalesRender\Plugin\Instance\Macros\Autocomplete;


use SalesRender\Plugin\Components\Form\Autocomplete\AutocompleteInterface;
use SalesRender\Plugin\Components\Translations\Translator;

class ExampleWithDeps implements AutocompleteInterface
{

    public function query(?string $query, array $dependencies, array $context): array
    {
        $query = trim($query);
        $values = [];
        for ($i = 0; $i < $dependencies[1]; $i++) {
            $n = $i + 1;
            $values["dynamic_with_dep_{$query}{$n}"] = [
                'title' => Translator::get('autocomplete', 'DYNAMIC_VALUE_WITH_DEP #{value}', ['value' => "{$query}{$n}"]),
                'group' => $this->getGroup($n . $query)
            ];
        }
        foreach ($context as $contextValue) {
            $values[$contextValue] = [
                'title' => $contextValue,
                'group' => 'context'
            ];
        }
        return $values;
    }

    public function values(array $values, array $dependencies, array $context): array
    {
        $result = [];

        sort($values);
        array_filter($values, function ($value) {
            return preg_match('~^dynamic_\d+$~', $value);
        });

        foreach ($values as $value) {
            $result[$value] = [
                'title' => Translator::get('autocomplete', 'DYNAMIC_VALUE_WITH_DEP #{value}', ['value' => $value]),
                'group' => $this->getGroup($value)
            ];
        }
        return $result;
    }

    public function validate(array $values, array $dependencies, array $context): bool
    {
        echo json_encode($context);
        foreach ($values as $value) {
            if (!preg_match('~^dynamic_\d+$~', $value) || !in_array($value, $context)) {
                return false;
            }
        }
        return true;
    }

    private function getGroup($value): string
    {
        $matches = [];
        if (preg_match('~(\d+)~', $value, $matches)) {
            $value = $matches[1];

            if ($value >= 1 && $value < 10) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 1, 'max' => 10]);
            }
        }

        return Translator::get('autocomplete', 'NO_GROUP');
    }

}