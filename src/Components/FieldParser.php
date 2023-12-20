<?php
/**
 * Created for plugin-exporter-excel
 * Date: 06.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace SalesRender\Plugin\Instance\Macros\Components;


use InvalidArgumentException;

class FieldParser
{

    private const REGEXP = '~(.+)\.\[([a-z\d_\.]+)=([^\]]+)\]\.(.+)~';

    /** @var string */
    private $field;

    /** @var string|null */
    private $left;

    /** @var string|null */
    private $right;

    /** @var string|null */
    private $property;

    /** @var string|null */
    private $value;

    public function __construct(string $field)
    {
        $this->field = $field;

        $matches = [];
        if (preg_match(self::REGEXP, $field, $matches)) {
            $this->left = $matches[1];
            $this->property = $matches[2];
            $this->value = $matches[3];
            $this->right = $matches[4];
        } else {
            throw new InvalidArgumentException('Simple field'); // todo
        }
    }

    public function getLeftPart(): string
    {
        return $this->left;
    }

    public function getRightPart(): string
    {
        return $this->right;
    }

    public function getPropertyPart(): string
    {
        return $this->left . '.' . $this->property;
    }

    public function getValuePart(): string
    {
        return $this->left . '.' . $this->right;
    }

    public function getFilterProperty(): ?string
    {
        return $this->property;
    }

    public function getFilterValue(): ?string
    {
        return $this->value;
    }

    public static function hasFilter(string $field): bool
    {
        return preg_match(self::REGEXP, $field);
    }

}