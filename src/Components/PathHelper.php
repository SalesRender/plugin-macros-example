<?php


namespace Leadvertex\Plugin\Instance\Macros\Components;


use Composer\Autoload\ClassLoader;
use ReflectionClass;
use XAKEPEHOK\Path\Path;

class PathHelper
{
    private static $root;

    public static function getRoot(): Path
    {
        if (self::$root === null) {
            $reflection = new ReflectionClass(ClassLoader::class);
            self::$root = (new Path($reflection->getFileName()))->up()->up()->up();
        }
        return self::$root;
    }
}