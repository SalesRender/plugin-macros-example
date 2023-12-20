<?php

namespace SalesRender\Plugin\Instance\Macros\Forms;


use SalesRender\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Limit;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\StaticValues;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnumDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Translations\Translator;

class SecondResponseOptionsForm extends Form
{

    public function __construct()
    {
        parent::__construct(
            Translator::get('second_response_options', 'OPTIONS_TITLE'),
            Translator::get('second_response_options', 'OPTIONS_DESCRIPTION'),
            [
                'second_response_options' => new FieldGroup(
                    Translator::get('second_response_options', 'GROUP_1'),
                    Translator::get('second_response_options', 'GROUP_1_DESCRIPTION'),
                    $this->getSecondResponseOptionsFields()
                )
            ],
            Translator::get(
                'response_options',
                'OPTIONS_BUTTON'
            )
        );
    }

    public function getSecondResponseOptionsFields($withDefault = true): array
    {
        $staticValidator = function ($values, ListOfEnumDefinition $definition) {
            $limit = $definition->getLimit();

            $errors = [];

            if (!is_null($values) && !is_array($values)) {
                $errors[] = Translator::get('response_options', 'LIST_OF_ENUM_VALIDATION_INVALID_ARGUMENT');
                return $errors;
            }

            if (is_null($values)) {
                $values = [];
            }

            if ($limit) {

                if ($limit->getMin() && count($values) < $limit->getMin()) {
                    $errors[] = Translator::get('response_options', 'LIST_OF_ENUM_VALIDATION_ERROR_MIN {min}', ['min' => $limit->getMin()]);
                }

                if ($limit->getMax() && count($values) > $limit->getMax()) {
                    $errors[] = Translator::get('response_options', 'LIST_OF_ENUM_VALIDATION_ERROR_MIN {max}', ['max' => $limit->getMax()]);
                }
            }

            $possibleValues = [
                'one',
                'two',
                'three'
            ];

            foreach ($values as $value) {
                if (!in_array($value, $possibleValues)) {
                    $errors[] = Translator::get('response_options', 'LIST_OF_ENUM_VALIDATION_ERROR {value}', ["value" => $value]);
                }
            }

            return $errors;
        };

        $setResponseValues = new StaticValues([
            "one" => [
                'title' => Translator::get('second_response_options', 'SET_ONE'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
            "two" => [
                'title' => Translator::get('second_response_options', 'SET_TWO'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
            "three" => [
                'title' => Translator::get('second_response_options', 'SET_THREE'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
        ]);

        return [
            'stringField' => new StringDefinition(
                Translator::get('second_response_options', 'SET_STRING_FIELD'),
                null,
                function ($value) {
                    $errors = [];
                    if (!is_string($value)) {
                        $errors[] = Translator::get('response_options', 'SET_STRING_ERROR');
                    }
                    return $errors;
                },
                ''
            ),
            'intField' => new IntegerDefinition(
                Translator::get('second_response_options', 'SET_INT_FIELD'),
                null,
                function ($value) {
                    $errors = [];
                    if (!is_int($value) || $value < 0) {
                        $errors[] = Translator::get('response_options', 'SET_INT_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 0 : null
            ),
            'boolField' => new BooleanDefinition(
                Translator::get('second_response_options', 'SET_BOOL_FIELD'),
                null,
                function ($value) {
                    $errors = [];
                    if (!is_bool($value)) {
                        $errors[] = Translator::get('response_options', 'SET_BOOL_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? false : null
            ),
            'enumField' => new ListOfEnumDefinition(
                Translator::get('second_response_options', 'SET_ENUM_FIELD'),
                null,
                $staticValidator,
                $setResponseValues,
                new Limit(1, 1),
                $withDefault ? ['one'] : null
            )
        ];
    }
}