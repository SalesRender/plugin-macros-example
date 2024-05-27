<?php

namespace SalesRender\Plugin\Instance\Macros\TablePreviewAction;


use SalesRender\Plugin\Components\Form\TableView\TablePreviewInterface;

class TablePreviewExcel implements TablePreviewInterface
{
    public function render(array $dependencies, array $context): array
    {
        $headers = [
            'Width',
            'Length',
            'Height'
        ];
        return [
            $headers,
            [1, 1, 1,],
            [20, 20, 20,],
            [35, 15],
            [26, null, 12],
            ['some', '', 'value'],
        ];
    }
}
