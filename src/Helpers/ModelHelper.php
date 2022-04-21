<?php

namespace Francerz\SqlBuilder\Helpers;

use Francerz\PowerData\Strings;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

final class ModelHelper
{
    public const PROPERTY_SKIP_DEFAULT  = 0b00001000;
    public const PROPERTY_SKIP_KEYS     = 0b00000001;
    public const PROPERTY_ONLY_KEYS     = 0b00000010;
    public const PROPERTY_KEEP_IGNORE   = 0b00010000;

    private function __construct()
    {
    }
    public static function dataAsArray($data, bool $withKeys = true): array
    {
        if (is_array($data)) {
            return $data;
        }
        if (!is_object($data)) {
            return [];
        }
        if ($data instanceof stdClass) {
            return (array)$data;
        }
        $flags = 0;
        $flags |= $withKeys ? 0 : static::PROPERTY_SKIP_KEYS;
        $props = static::getDataProperties($data, $flags);

        $arr = [];
        foreach ($props as $prop) {
            $name = $prop->getName();
            $arr[$name] = $data->{$name};
        }
        return $arr;
    }

    /** @return ReflectionProperty[] */
    public static function getDataProperties($obj, int $flags = 0)
    {
        if (!is_object($obj)) {
            return [];
        }
        if ($obj instanceof stdClass) {
            return [];
        }
        $reflection = new ReflectionClass($obj);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $props = array_filter($properties, function (ReflectionProperty $prop) use ($flags) {
            $comment = $prop->getDocComment();

            if (($flags & static::PROPERTY_ONLY_KEYS) > 0) {
                if (!is_string($comment)) {
                    return false;
                }
                return Strings::contains($comment, '@sql-key');
            }

            if ($comment === false) {
                return true;
            }
            if (Strings::contains($comment, '@sql-ignore')) {
                return ($flags & static::PROPERTY_KEEP_IGNORE) > 0;
            }
            if (Strings::contains($comment, '@sql-key')) {
                return ($flags & static::PROPERTY_SKIP_KEYS) === 0;
            }

            return ($flags & static::PROPERTY_SKIP_DEFAULT) === 0;
        });

        return $props;
    }
}
