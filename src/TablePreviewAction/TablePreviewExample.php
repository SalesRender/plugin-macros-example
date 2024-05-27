<?php

namespace SalesRender\Plugin\Instance\Macros\TablePreviewAction;


use SalesRender\Plugin\Components\Form\TableView\TablePreviewInterface;

class TablePreviewExample implements TablePreviewInterface
{

    public function render(array $dependencies, array $context): array
    {
        $result = [['key', 'dep', 'sum']];
        $sum = 0;
        foreach ($dependencies as $key => $dependency) {
            $sum += $dependency;
            $result[] = ['dep ' . ($key + 1), $dependency, $sum];
        }

        return $result;
    }
}