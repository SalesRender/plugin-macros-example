<?php

namespace Leadvertex\Plugin\Instance\Macros\Forms;


use Leadvertex\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Limit;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\StaticValues;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnumDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use Leadvertex\Plugin\Components\Form\FieldGroup;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Instance\Macros\Components\OptionsSingletonTrait;

class ResponseOptionsForm extends Form
{
    use OptionsSingletonTrait;

    public function __construct()
    {
        parent::__construct(
            Translator::get('response_options', 'OPTIONS_TITLE'),
            Translator::get('response_options', 'OPTIONS_DESCRIPTION'),
            [
                'response_options' => new FieldGroup(
                    Translator::get('response_options', 'GROUP_1'),
                    Translator::get('response_options', 'GROUP_1_DESCRIPTION'),
                    $this->getResponseOptionsFields()
                )
            ],
            Translator::get(
                'response_options',
                'OPTIONS_BUTTON'
            )
        );
    }

    public function getResponseOptionsFields($withDefault = true): array
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
                'static_uri',
                'static_success',
                'static_error'
            ];

            foreach ($values as $value) {
                if (!in_array($value, $possibleValues)) {
                    $errors[] = Translator::get('response_options', 'LIST_OF_ENUM_VALIDATION_ERROR {value}', ["value" => $value]);
                }
            }

            return $errors;
        };

        $setResponseValues = [
            "static_success" => [
                'title' => Translator::get('response_options', 'SET_RESPONSE_SUCCESS_VALUE_TITLE'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
            "static_uri" => [
                'title' => Translator::get('response_options', 'SET_RESPONSE_URL_VALUE_TITLE'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
            "static_error" => [
                'title' => Translator::get('response_options', 'SET_RESPONSE_ERROR_VALUE_TITLE'),
                'group' => Translator::get('response_options', 'Only {limit}', ['limit' => 1]),
            ],
        ];

        $setResponseValues = new StaticValues($setResponseValues);

        return [
            'description' => new StringDefinition(
                Translator::get('response_options', 'SET_PROCESS_DESCRIPTION'),
                null,
                function ($value) {
                    $errors = [];
                    if (!is_string($value)) {
                        $errors[] = Translator::get('response_options', 'SET_PROCESS_DESCRIPTION_ERROR');
                    }

                    if (trim($value) === '') {
                        $errors[] = Translator::get('response_options', 'SET_PROCESS_DESCRIPTION_ERROR');
                    }

                    return $errors;
                },
                ''
            ),
            'errors' => new IntegerDefinition(
                Translator::get('response_options', 'SET_ERRORS_COUNT_FIELD_TITLE'),
                Translator::get('response_options', 'SET_ERRORS_COUNT_FIELD_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_int($value) || $value < 0) {
                        $errors[] = Translator::get('response_options', 'SET_ERRORS_COUNT_FIELD_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 0 : null
            ),
            'skipped' => new IntegerDefinition(
                Translator::get('response_options', 'SET_SKIPPED_COUNT_FIELD_TITLE'),
                Translator::get('response_options', 'SET_SKIPPED_COUNT_FIELD_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_int($value) || $value < 0) {
                        $errors[] = Translator::get('response_options', 'SET_SKIPPED_COUNT_FIELD_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 0 : null
            ),
            'delay' => new IntegerDefinition(
                Translator::get('response_options', 'SET_PROCESSING_DELAY_FIELD_TITLE'),
                Translator::get('response_options', 'SET_PROCESSING_DELAY_FIELD_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_int($value) || $value < 0) {
                        $errors[] = Translator::get('response_options', 'SET_PROCESSING_DELAY_FIELD_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 0 : null
            ),
            'post_processing_time' => new IntegerDefinition(
                Translator::get('response_options', 'SET_POST_PROCESSING_TIME_TITLE'),
                Translator::get('response_options', 'SET_POST_PROCESSING_TIME_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_int($value) || $value < 0) {
                        $errors[] = Translator::get('response_options', 'SET_POST_PROCESSING_TIME_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 10 : null
            ),
            'nullCount' => new BooleanDefinition(
                Translator::get('response_options', 'SET_NULL_ORDER_COUNT_FIELD_TITLE'),
                Translator::get('response_options', 'SET_NULL_ORDER_COUNT_FIELD_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_bool($value)) {
                        $errors[] = Translator::get('response_options', 'SET_NULL_ORDER_COUNT_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? false : null
            ),
            'response' => new ListOfEnumDefinition(
                Translator::get('response_options', 'SET_RESPONSE_TITLE'),
                Translator::get('response_options', 'SET_RESPONSE_DESCRIPTION'),
                $staticValidator,
                $setResponseValues,
                new Limit(1, 1),
                $withDefault ? ['static_success'] : null
            )
        ];
    }
}