<?php


namespace Leadvertex\Plugin\Instance\Macros\Forms;


use Leadvertex\Plugin\Components\Form\FieldGroup;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Models\Session;
use Leadvertex\Plugin\Instance\Macros\Components\OptionsSingletonTrait;
use Leadvertex\Plugin\Instance\Macros\Plugin;

class PreviewOptionsForm extends Form
{
    use OptionsSingletonTrait;

    private function __construct()
    {
        $options = Session::current()->getOptions(1);
        if (!$options->isEmpty()) {
            if (!$options->get('response_options.nullCount', true)) {
                $queryResult = Plugin::getOrdersWithFsp(Session::current()->getFsp());
                if ($queryResult['success']) {
                    $groupDescription = Translator::get(
                        'orders_to_process_options',
                        'GROUP_1_ORDERS_DESCRIPTION {ordersCount} {ordersTable}',
                        [
                            'ordersCount' => count($queryResult['data']),
                            'ordersTable' => $this->generateMarkdownTableForOrdersIds($queryResult['data'])
                        ]
                    );
                } else {
                    $groupDescription = Translator::get(
                        'orders_to_process_options',
                        'GROUP_1_QUERY_ERROR_DESCRIPTION {errors}',
                        ['errors' => json_encode($queryResult['errors'])]
                    );
                }
            } else {
                $groupDescription = Translator::get(
                    'orders_to_process_options',
                    'GROUP_1_NO_ORDERS_DESCRIPTION'
                );
            }
        } else {
            $groupDescription = Translator::get(
                'orders_to_process_options',
                'GROUP_1_NO_ORDERS_DESCRIPTION'
            );
        }

        parent::__construct(
            Translator::get('orders_to_process_options', 'OPTIONS_TITLE'),
            Translator::get('orders_to_process_options', 'OPTIONS_DESCRIPTION'),
            [
                'orders_to_process_options' => new FieldGroup(
                    Translator::get('orders_to_process_options', 'GROUP_1'),
                    $groupDescription,
                    []
                )
            ],
            Translator::get(
                'orders_to_process_options',
                'OPTIONS_BUTTON'
            )
        );
    }

    private function generateMarkdownTableForOrdersIds(array $orders): string
    {
        $tableContent = '';
        $tableHeader = <<<MARKDOWN
|Orders ids|Status name|
|---|---|

MARKDOWN;
        foreach ($orders as $order) {
            $tableContent .= <<<MARKDOWN
|{$order['id']}|{$order['status']['name']}|

MARKDOWN;
        }
        $tableContent = substr($tableContent, 0, -2);
        return $tableHeader . $tableContent;
    }
}