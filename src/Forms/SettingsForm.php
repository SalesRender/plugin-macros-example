<?php
/**
 * Created for plugin-exporter-excel
 * Datetime: 03.03.2020 15:43
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace SalesRender\Plugin\Instance\Macros\Forms;


use SalesRender\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\FileDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\FloatDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\IFrameDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Limit;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\DynamicValues;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\StaticValues;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnumDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\MarkdownDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\PasswordDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\TablePreviewField;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Translations\Translator;
use SalesRender\Plugin\Instance\Macros\Components\Columns;
use XAKEPEHOK\Path\Path;

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
                    $this->getFieldsArray()
                ),
                'group_2' => new FieldGroup(
                    Translator::get('settings', 'GROUP_2'),
                    null,
                    array_reverse($this->getFieldsArray(false))
                ),
                'group_3' => new FieldGroup(
                    Translator::get('settings', 'GROUP_3'),
                    Translator::get('settings', 'GROUP_3_DESCRIPTION'). "\n" . file_get_contents(Path::root()->down('markdown.md')),
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
        $columns = new Columns();
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
        $staticValidator = function ($values, ListOfEnumDefinition $definition) {
            $limit = $definition->getLimit();

            $errors = [];

            if (!is_null($values) && !is_array($values)) {
                $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_INVALID_ARGUMENT');
                return $errors;
            }

            if (is_null($values)) {
                $values = [];
            }

            if ($limit) {

                if ($limit->getMin() && (count($values) < $limit->getMin())) {
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
            'fields' => new ListOfEnumDefinition(
                Translator::get(
                    'settings',
                    'Столбцы'
                ),
                Translator::get(
                    'settings',
                    'Выберите данные, которые вы хотите выгружать'
                ),
                function ($values) use ($columns) {
                    if (!is_array($values)) {
                        return [Translator::get(
                            'errors',
                            'Некорректное значение'
                        )];
                    }

                    $errors = [];
                    if (count($values) < 1) {
                        $errors[] = Translator::get(
                            'errors',
                            'Необходимо выбрать минимум одно поле для выгрузки'
                        );
                    }

                    foreach ($values as $value) {
                        if (!isset($columns->getList()[$value])) {
                            $errors[] = Translator::get(
                                'errors',
                                'Выбрано несуществующее поле "{field}"',
                                ['field' => $value]
                            );
                        }
                    }

                    return $errors;
                },
                new StaticValues($columns->getList()),
                new Limit(1, null),
                $withDefault ? ['id', 'createdAt', 'cart.total'] : null
            ),
            'bool_field' => new BooleanDefinition(
                Translator::get('settings', 'BOOL_TITLE'),
                Translator::get('settings', 'BOOL_DESCRIPTION'),
                function ($value) {
                    $errors = [];
                    if (!is_bool($value)) {
                        $errors[] = Translator::get('settings', 'BOOL_VALIDATION_ERROR');
                    }
                    if (is_null($value)) {
                        $errors[] = Translator::get('settings', 'NULL_ERROR');
                    }
                    return $errors;
                },
                $withDefault ? true : null
            ),
            'float_field' => new FloatDefinition(
                Translator::get('settings', 'FLOAT_TITLE'),
                Translator::get('settings', 'FLOAT_DESCRIPTION'),
                function ($value) {
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
                function ($value) {
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
                function ($value) {
                    $errors = [];

                    if (!is_scalar($value) && !is_null($value)) {
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
                function ($value) {
                    $errors = [];

                    if (trim($value) === '') {
                        $errors[] = Translator::get('settings', 'STRING_VALIDATION_INVALID_ARGUMENT');
                    }

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
            'markdown_field' => new MarkdownDefinition(
                Translator::get('settings', 'MARKDOWN_TITLE'),
                Translator::get('settings', 'MARKDOWN_DESCRIPTION'),
                function ($value) {
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
                function ($value) {
                    $errors = [];

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $value,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                    ));
                    curl_exec($curl);
                    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);

                    if ($code !== 200) {
                        $errors[] = Translator::get('settings', 'FILE_VALIDATION_ERROR');
                    }

                    return $errors;
                },
                $withDefault ? 'https://leadvertex.ru/img/logo.png' : null
            ),
            'listOfEnum_field_dynamic' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                function ($values) {
                    $errors = [];

                    if (!is_null($values) && !is_array($values)) {
                        $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_INVALID_ARGUMENT');
                        return $errors;
                    }

                    if (is_null($values)) {
                        $values = [];
                    }

                    foreach ($values as $value) {
                        if (!preg_match('~^dynamic_\d+$~', $value)) {
                            $errors[] = Translator::get('settings', 'LIST_OF_ENUM_VALIDATION_ERROR {value}');
                        }
                    }
                    return $errors;
                },
                new DynamicValues('example'),
                new Limit(2, null),
                $withDefault ? ['dynamic_10', 'dynamic_1'] : null
            ),
            'listOfEnum_field_static' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                null,
                $withDefault ? ['static_1', 'static_2', 'static_3'] : null
            ),
            'listOfEnum_field_static_min_only' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(2, null),
                $withDefault ? ['static_1', 'static_2'] : null
            ),
            'listOfEnum_field_static_max_only' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(null, 5),
                $withDefault ? ['static_1'] : null
            ),
            'listOfEnum_field_static_min_and_max' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(2, 5),
                $withDefault ? ['static_1', 'static_2', 'static_3', 'static_4', 'static_5'] : null
            ),
            'listOfEnum_field_static_one' => new ListOfEnumDefinition(
                Translator::get('settings', 'LIST_OF_ENUM_TITLE'),
                Translator::get('settings', 'LIST_OF_ENUM_DESCRIPTION'),
                $staticValidator,
                $staticValues,
                new Limit(1, 1),
                $withDefault ? ['static_3'] : null
            ),
            'iframe_field' => new IFrameDefinition(
                Translator::get('settings', 'IFRAME_TITLE'),
                Translator::get('settings', 'IFRAME_DESCRIPTION'),
                function ($value) {
                    if ($value < 0 || $value > 10) {
                        $errors[] = Translator::get('settings', 'INTEGER_VALIDATION_ERROR');
                    }
                    return [];
                },
                'iframe/example.html',
                $withDefault ? '5' : null
            ),
            'iframe_field_second' => new IFrameDefinition(
                Translator::get('settings', 'IFRAME_TITLE'),
                Translator::get('settings', 'IFRAME_DESCRIPTION'),
                function ($value) {
                    if ($value < 0 || $value > 10) {
                        $errors[] = Translator::get('settings', 'INTEGER_VALIDATION_ERROR');
                    }
                    return [];
                },
                'iframe/example.html',
                $withDefault ? '1' : null,
            ),
            'tablePreview' => new TablePreviewField(
                Translator::get('settings', 'TABLE_PREVIEW_TITLE'),
                Translator::get('settings', 'TABLE_PREVIEW_DESCRIPTION'),
                'excel',
                'default text',
                'context?'
            )
        ];
    }

}