<?php

namespace Leadvertex\Plugin\Instance\Macros\Forms;


use Adbar\Dot;
use Leadvertex\Plugin\Components\Batch\Batch;
use Leadvertex\Plugin\Components\Form\FieldGroup;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Token\GraphqlInputToken;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Instance\Macros\Components\Columns;
use Leadvertex\Plugin\Instance\Macros\Components\OptionsSingletonTrait;
use XAKEPEHOK\ArrayGraphQL\ArrayGraphQL;

class PreviewOptionsForm extends Form
{
    use OptionsSingletonTrait;

    private $orders;
    private $fsp;
    private $fields;
    private $batch;


    private function __construct()
    {
        $this->fields = GraphqlInputToken::getInstance()->getSettings()->getData()->get('group_1.fields');
        $this->batch = Batch::findById(GraphqlInputToken::getInstance()->getId());
        $this->fsp = $this->batch->getFsp();
        $this->orders = $this->getOrders();

        if (!is_null($this->orders)) {
            $groupDescription = Translator::get(
                'orders_to_process_options',
                'GROUP_1_ORDERS_DESCRIPTION {ordersCount} {ordersTable}',
                [
                    'ordersCount' => count($this->orders),
                    'ordersTable' => $this->generateMarkdownTableForOrders()
                ]
            );
        } else {
            $groupDescription = Translator::get(
                'orders_to_process_options',
                'GROUP_1_QUERY_ERROR_DESCRIPTION {errors}'
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

    private function generateMarkdownTableForOrders(): string
    {
        $orders = $this->orders;
        $fields = GraphqlInputToken::getInstance()->getSettings()->getData()->get('group_1.fields');
        $tableContent = '';
        $tableKey = '';
        $tableSecondRow = '';
        foreach ($fields as $field) {
            $tableSecondRow .= "|---";
            $tableKey .= "|{$field}";
        }
        foreach ($orders as $order) {
            $order = (new Dot($order));
            $tableData = '';
            foreach ($fields as $field) {
                $field = preg_replace('/\[[^\]]+]/', '0', $field);
                $tableData .= "|{$order->get($field)} ";
            }
            $tableContent .= "{$tableData}|" . PHP_EOL;
        }

        return PHP_EOL . $tableKey . '|' . PHP_EOL . $tableSecondRow . '|' . PHP_EOL . $tableContent;
    }

    private function getQuery(): string
    {
        $fields = ArrayGraphQL::convert(Columns::getQueryColumns($this->fields));
        return '
            query($pagination: Pagination!, $filters: OrderFilter, $sort: OrderSort) {
                ordersFetcher(pagination: $pagination, filters: $filters, sort: $sort) ' . $fields . '
            }
        ';
    }

    private function getVariables(): array
    {
        $fsp = [
            'pagination' => [
                'pageNumber' => 1,
                'pageSize' => 5
            ]
        ];

        if ($this->fsp->getFilters()) {
            $fsp['filters'] = $this->fsp->getFilters();
        }

        if ($this->fsp->getSort()) {
            $fsp['sort'] = [
                'field' => $this->fsp->getSort()->getField(),
                'direction' => $this->fsp->getSort()->getDirection(),
            ];
        }

        return $fsp;
    }

    private function getOrders(): array
    {
        $apiClient = $this->batch->getApiClient();
        $response = new Dot($apiClient->query($this->getQuery(), $this->getVariables())->getData());
        return $response->get('ordersFetcher.orders');
    }
}