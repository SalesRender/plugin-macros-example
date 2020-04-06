<?php
/**
 * Created for plugin-exporter-excel
 * Datetime: 03.03.2020 15:43
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Instance\Macros\Forms;


use Leadvertex\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\FieldDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\FileDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\FloatDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Limit;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\DynamicValues;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\StaticValues;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\ListOfEnumDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\MarkdownDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\PasswordDefinition;
use Leadvertex\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use Leadvertex\Plugin\Components\Form\FieldGroup;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Translations\Translator;

class SettingsForm extends Form
{

    public function __construct()
    {
        parent::__construct(
            Translator::get(
                'settings',
                'SETTINGS_TITLE'
            ),
            Translator::get(
                'settings',
                'SETTINGS_DESCRIPTION'
            ),
            [
                'group_1' => new FieldGroup(
                    Translator::get('settings', 'GROUP_1'),
                    Translator::get('settings', 'GROUP_1_DESCRIPTION'),
                    $this->getFieldsArray(true)
                ),
                'group_2' => new FieldGroup(
                    Translator::get('settings', 'GROUP_2'),
                    null,
                    array_reverse($this->getFieldsArray(false))
                ),
                'group_3' => new FieldGroup(
                    Translator::get('settings', 'GROUP_3'),
                    Translator::get('settings', 'GROUP_3_DESCRIPTION'),
                    []
                ),
            ],
            Translator::get(
                'settings',
                'SETTINGS_BUTTON'
            )
        );
    }

    protected function getFieldsArray($withDefault = true): array
    {
        $values = [];
        for ($i = 1; $i <= 10; $i++) {
            $values["static_{$i}"] = [
                'title' => Translator::get('settings', 'Value #{value}', ['value' => $i]),
                'group' => Translator::get('settings', 'From {min} to {max}', ['min' => 1, 'max' => 10]),
            ];
        }

        for ($i = 11; $i <= 20; $i++) {
            $values["static_{$i}"] = [
                'title' => Translator::get('settings', 'Value #{value}', ['value' => $i]),
                'group' => Translator::get('settings', 'From {min} to {max}', ['min' => 11, 'max' => 20]),
            ];
        }

        $staticValues = new StaticValues($values);
        $staticValidator = function ($values, ListOfEnumDefinition $definition, Form $form) {
            $limit = $definition->getLimit();

            $errors = [];

            if (!is_null($values) && !is_array($values)) {
                $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_INVALID_ARGUMENT');
                return $errors;
            }

            if ($limit) {

                if ($limit->getMin() && count($values) < $limit->getMin()) {
                    $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_ERROR_MIN {min}', ['min' => $limit->getMin()]);
                }

                if ($limit->getMax() && count($values) > $limit->getMax()) {
                    $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_ERROR_MIN {max}', ['max' => $limit->getMax()]);
                }
            }

            $possibleValues = [];
            for ($i = 1; $i <= 20; $i++) {
                $possibleValues[] = 'static_' . $i;
            }

            foreach ($values as $value) {
                if (!in_array($value, $possibleValues)) {
                    $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_ERROR {value}');
                }
            }

            return $errors;
        };

        return [
            'bool_field' => new BooleanDefinition(
                Translator::get('settings', 'BOOL_TITLE'),
                Translator::get('settings', 'BOOL_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];
                    if (!is_bool($value)) {
                        $errors[] = Translator::get('settings', 'BOOL_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? true : null
            ),
            'float_field' => new FloatDefinition(
                Translator::get('settings', 'FLOAT_TITLE'),
                Translator::get('settings', 'FLOAT_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];
                    if (!is_numeric($value)) {
                        $errors[] = Translator::get('settings', 'FLOAT_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 10.5 : null
            ),
            'integer_field' => new IntegerDefinition(
                Translator::get('settings', 'INTEGER_TITLE'),
                Translator::get('settings', 'INTEGER_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];
                    if (!is_int($value)) {
                        $errors[] = Translator::get('settings', 'INTEGER_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? 100 : null
            ),
            'password_field' => new PasswordDefinition(
                Translator::get('settings', 'PASSWORD_TITLE'),
                Translator::get('settings', 'PASSWORD_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];

                    if (!is_scalar($value)) {
                        $errors[] = Translator::get('settings', 'PASSWORD_VALIDATION_NOT_STRING');
                    }

                    if (mb_strlen($value) < 6) {
                        $errors[] = Translator::get('settings', 'PASSWORD_VALIDATION_TOO_SHORT');
                    }

                    if (!preg_match('~(?=.*[a-z])~', $value)) {
                        $errors[] = Translator::get('settings', 'PASSWORD_VALIDATION_NO_LOWERCASE_CHARS');
                    }

                    if (!preg_match('~(?=.*[A-Z])~', $value)) {
                        $errors[] = Translator::get('settings', 'PASSWORD_VALIDATION_NO_UPPERCASE_CHARS');
                    }

                    if (!preg_match('~(?=.*[0-9])~', $value)) {
                        $errors[] = Translator::get('settings', 'PASSWORD_VALIDATION_NO_DIGIT_CHARS');
                    }

                    return $errors;
                },
                $withDefault ? 'P@$$w0RD' : null
            ),
            'string_field' => new StringDefinition(
                Translator::get('settings', 'STRING_TITLE'),
                Translator::get('settings', 'STRING_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];

                    if (!is_scalar($value)) {
                        $errors[] = Translator::get('settings', 'STRING_VALIDATION_INVALID_ARGUMENT');
                    }

                    if (mb_strlen($value) > 200) {
                        $errors[] = Translator::get('settings', 'STRING_VALIDATION_TOO_LONG');
                    }

                    return $errors;
                },
                $withDefault ? 'support@leadvertex.ru' : null
            ),
            'markdwn_field' => new MarkdownDefinition(
                Translator::get('settings', 'MARKDOWN_TITLE'),
                Translator::get('settings', 'MARKDOWN_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];
                    if (!is_string($value)) {
                        $errors[] = Translator::get('settings', 'MARKDOWN_VALIDATION_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? '**hello** world' : null
            ),
            'file_field' => new FileDefinition(
                Translator::get('settings', 'FILE_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                function ($value, FieldDefinition $definition, Form $form) {
                    $errors = [];

                    $ch = curl_init((string) $value);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if ($code !== 200) {
                        $errors[] = Translator::get('settings', 'FILE_VALIDATION_ERROR');
                    }

                    return $errors;
                },
                $withDefault ? 'https://leadvertex.ru/img/logo.png' : null
            ),
            'listOfEnum_field_dynamic' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                function ($values, FieldDefinition $definition, Form $form) {
                    $errors = [];

                    if (!is_null($values) && !is_array($values)) {
                        $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_INVALID_ARGUMENT');
                        return $errors;
                    }

                    foreach ($values as $value) {
                        if (!preg_match('~^dynamic_\d+$~', $value)) {
                            $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_ERROR {value}');
                        }
                    }
                    return $errors;
                },
                new DynamicValues($_ENV['LV_PLUGIN_SELF_URI'] . 'autocomplete/example'),
                null,
                $withDefault ? ['dynamic_10', 'dynamic_1'] : null
            ),
            'listOfEnum_field_static' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                null,
                $withDefault ? ['static_1', 'static_2', 'static_3'] : null
            ),
            'listOfEnum_field_static_min_only' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(2, null),
                $withDefault ? ['static_1', 'static_2'] : null
            ),
            'listOfEnum_field_static_max_only' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(null, 5),
                $withDefault ? ['static_1'] : null
            ),
            'listOfEnum_field_static_min_and_max' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(2, 5),
                $withDefault ? ['static_1', 'static_2', 'static_3', 'static_4', 'static_5'] : null
            ),
            'listOfEnum_field_static_one' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'FILE_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(1, 1),
                $withDefault ? ['static_3'] : null
            ),
        ];
    }

}