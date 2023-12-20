<?php
/**
 * Created for plugin-exporter-excel
 * Datetime: 30.07.2019 16:20
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace SalesRender\Plugin\Instance\Macros\Components;


use SalesRender\Plugin\Components\ApiClient\ApiFetcherIterator;
use XAKEPEHOK\ArrayGraphQL\ArrayGraphQL;
use XAKEPEHOK\ArrayGraphQL\InvalidArrayException;

class OrdersFetcherIterator extends ApiFetcherIterator
{

    /**
     * @param array $body
     * @return string
     * @throws InvalidArrayException
     */
    public function getQuery(array $body): string
    {
        $fields = ArrayGraphQL::convert($body);
        return '
            query($pagination: Pagination!, $filters: OrderSearchFilter, $sort: OrderSort) {
                ordersFetcher(pagination: $pagination, filters: $filters, sort: $sort) ' . $fields . '
            }
        ';
    }

    /**
     * Dot-notation string to query body
     * @return string
     */
    protected function getQueryPath(): string
    {
        return 'ordersFetcher';
    }

    protected function getIdentity(array $array): string
    {
        return $array['id'];
    }
}