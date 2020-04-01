<?php
/**
 * Created for plugin-macros-example
 * Date: 30.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Instance\Macros\Autocomplete;


use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Components\AutocompleteInterface;

class Example implements AutocompleteInterface
{

    public function query(string $query): array
    {
        $values = [];
        if (preg_match('~^\d+$~', $query)) {

            $values["dynamic_{$query}"] = [
                'title' => Translator::get('autocomplete', 'DYNAMIC_VALUE #{value}', ['value' => $query]),
                'group' => $this->getGroup($query)
            ];

            for ($i = 1; $i < 10; $i++) {

                $values["dynamic_{$i}{$query}"] = [
                    'title' => Translator::get('autocomplete', 'DYNAMIC_VALUE #{value}', ['value' => "{$i}{$query}"]),
                    'group' => $this->getGroup($i . $query)
                ];
            }
        }
        return $values;
    }

    public function values(array $values): array
    {
        $result = [];

        sort($values);
        array_filter($values, function ($value) {
            return preg_match('~^dynamic_\d+$~', $value);
        });

        foreach ($values as $value) {
            $result[$value] = [
                'title' => Translator::get('autocomplete', 'DYNAMIC_VALUE #{value}', ['value' => $value]),
                'group' => $this->getGroup($value)
            ];
        }
        return $result;
    }

    public function validate(array $values): bool
    {
        foreach ($values as $value) {
            if (!preg_match('~^dynamic_\d+$~', $value)) {
                return false;
            }
        }
        return true;
    }

    private function getGroup($value)
    {
        $matches = [];
        if (preg_match('~(\d+)~', $value, $matches)) {
            $value = $matches[1];

            if ($value >= 1 && $value < 10) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 1, 'max' => 10]);
            }

            if ($value >= 11 && $value < 100) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 11, 'max' => 100]);
            }

            if ($value >= 101 && $value < 1000) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 101, 'max' => 1000]);
            }

            if ($value >= 1001 && $value < 10000) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 1001, 'max' => 10000]);
            }

            if ($value >= 10001 && $value < 100000) {
                return Translator::get('autocomplete', 'GROUP_FROM_TO ({min}-{max})', ['min' => 10001, 'max' => 100000]);
            }

            if ($value >= 100000) {
                return Translator::get('autocomplete', 'GROUP_OVER ({max})', ['max' => 100000]);
            }
        }

        return Translator::get('autocomplete', 'NO_GROUP');
    }

}