<?php

namespace SalesRender\Plugin\Instance\Macros\TablePreviewAction;


use SalesRender\Plugin\Components\Form\TableView\TablePreviewInterface;

class TablePreviewExcel implements TablePreviewInterface
{
    public function render(array $dependencies, array $context): array
    {
        return ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'some', 'value', 'excel'];
    }
}