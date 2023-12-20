<?php

namespace SalesRender\Plugin\Instance\Macros\Forms;


use Adbar\Dot;
use SalesRender\Plugin\Components\Access\Token\GraphqlInputToken;
use SalesRender\Plugin\Components\Batch\Batch;
use SalesRender\Plugin\Components\Db\Model;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Components\Translations\Translator;
use SalesRender\Plugin\Instance\Macros\Components\Columns;
use XAKEPEHOK\ArrayGraphQL\ArrayGraphQL;

class PreviewOptionsForm extends Form
{

    private array $orders;
    private $fsp;
    private $fields;
    private ?Model $batch;


    public function __construct()
    {
        $settings = Settings::find();
        $defaultFormat = $settings->getData()->get('group_1.fields');

        if (is_null($defaultFormat)) {
            $settings::guardIntegrity();
        }

        $this->batch = Batch::findById(GraphqlInputToken::getInstance()->getId());
        $this->fields = Settings::find()->getData()->get('group_1.fields');
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
        $fields = $this->fields;
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
                $field = preg_replace('/\[[^]]+]/', '0', $field);
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
            query($pagination: Pagination!, $filters: OrderSearchFilter, $sort: OrderSort) {
                ordersFetcher(pagination: $pagination, filters: $filters, sort: $sort) ' . $fields . '
            }
        ';
    }

    private function getVariables(int $pageNumber = 1): array
    {
        $fsp = [
            'pagination' => [
                'pageNumber' => $pageNumber,
                'pageSize' => $this->fsp->getPageSize()
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