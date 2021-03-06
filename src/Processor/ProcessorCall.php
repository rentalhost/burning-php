<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningAutoloader;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class ProcessorCall
{
    use SingletonPatternTrait;

    public const
        STRING_COMPOSITION_GENERIC = 'g',
        STRING_COMPOSITION_EMPTY = 'e',
        STRING_COMPOSITION_BOOLEAN = 'b',
        STRING_COMPOSITION_INTEGER = 'i',
        STRING_COMPOSITION_FLOAT = 'f',

        STRING_LENGTH_DECLARATION = 'l',

        ARRAY_COMPOSITION_MIXED = 'm',
        ARRAY_COMPOSITION_STRING = 's',
        ARRAY_COMPOSITION_INTEGER = 'i',
        ARRAY_COMPOSITION_FLOAT = 'f',
        ARRAY_COMPOSITION_BOOLEAN = 'b',
        ARRAY_COMPOSITION_ARRAY = 'a',
        ARRAY_COMPOSITION_OBJECT = 'o',
        ARRAY_COMPOSITION_OBJECT_MIXED = 'om',
        ARRAY_COMPOSITION_NULL = 'N',
        ARRAY_COMPOSITION_RESOURCE = 'r',
        ARRAY_COMPOSITION_RESOURCE_MIXED = 'rm',

        OBJECT_TYPE_ANONYMOUS = 'a';

    /**
     * @param mixed $variable
     * @return mixed
     */
    public static function annotateVariable(string $filePath, int $statementIndex, $variable)
    {
        switch (gettype($variable)) {
            case 'string':
                $variableType      = 's';
                $variableArguments = [];

                $variableTypeStringLength = strlen($variable);

                if ($variableTypeStringLength > 255) {
                    $variableArguments[] = self::STRING_LENGTH_DECLARATION;
                    $variableArguments[] = $variableTypeStringLength;
                }
                else if ($variableTypeStringLength > 0) {
                    $variableArguments[] = ProcessorStrings::getInstance()->getStringIndex($variable);
                }
                break;
            case 'NULL':
                $variableType      = 'N';
                $variableArguments = [];
                break;
            case 'integer':
                $variableType      = 'i';
                $variableArguments = [ $variable ];
                break;
            case 'float':
            case 'double':
                $variableType      = 'f';
                $variableArguments = [ $variable ];
                break;
            case 'boolean':
                $variableType      = 'b';
                $variableArguments = [ (int) $variable ];
                break;
            case 'array':
                $variableType      = 'a';
                $variableArguments = [ count($variable) ];

                if ($variable) {
                    $variableTypeArrayComposition = self::getArrayComposition($variable);
                    $variableArguments[]          = $variableTypeArrayComposition;

                    if ($variableTypeArrayComposition === self::ARRAY_COMPOSITION_BOOLEAN) {
                        $variableTypeBooleanTrue = 0;

                        foreach ($variable as $variableItem) {
                            $variableTypeBooleanTrue += $variableItem === true;
                        }

                        $variableArguments[] = $variableTypeBooleanTrue;
                        $variableArguments[] = count($variable) - $variableTypeBooleanTrue;
                    }
                    else if ($variableTypeArrayComposition === self::ARRAY_COMPOSITION_STRING) {
                        $variableItemsUnique       = array_unique($variable);
                        $variableItemsUniqueCount  = count($variableItemsUnique);
                        $variableItemsUniqueInline = $variableItemsUniqueCount <= 8;

                        if ($variableItemsUniqueInline) {
                            foreach ($variable as $variableItem) {
                                if (strlen($variableItem) > 255) {
                                    $variableItemsUniqueInline = false;

                                    break;
                                }
                            }
                        }

                        if ($variableItemsUniqueInline) {
                            $processorStrings = ProcessorStrings::getInstance();

                            if (count(array_filter($variableItemsUnique, [ self::class, 'isEmptyString' ])) === $variableItemsUniqueCount) {
                                $variableArguments[] = self::STRING_COMPOSITION_EMPTY;
                            }
                            else if (count(array_filter($variableItemsUnique, 'ctype_digit')) === $variableItemsUniqueCount) {
                                $variableArguments[] = self::STRING_COMPOSITION_INTEGER;
                                $variableArguments   = array_merge($variableArguments, $variableItemsUnique);
                            }
                            else if (count(array_filter($variableItemsUnique, [ self::class, 'isFloatString' ])) === $variableItemsUniqueCount) {
                                $variableArguments[] = self::STRING_COMPOSITION_FLOAT;
                                $variableArguments   = array_merge($variableArguments, $variableItemsUnique);
                            }
                            else {
                                $variableArguments = array_merge($variableArguments, array_map([ $processorStrings, 'getStringIndex' ], $variableItemsUnique));
                            }
                        }
                        else {
                            $variableTypeStringComposition = self::getStringComposition(self::getArrayFirstElement($variable));

                            foreach ($variable as $variableItem) {
                                if (self::getStringComposition($variableItem) !== $variableTypeStringComposition) {
                                    $variableTypeStringComposition = self::STRING_COMPOSITION_GENERIC;
                                }
                            }

                            $variableArguments[] = $variableTypeStringComposition;
                        }
                    }
                    else if ($variableTypeArrayComposition === self::ARRAY_COMPOSITION_INTEGER ||
                             $variableTypeArrayComposition === self::ARRAY_COMPOSITION_FLOAT) {
                        $variableItemsUnique      = array_unique($variable);
                        $variableItemsUniqueCount = count($variableItemsUnique);

                        if ($variableItemsUniqueCount <= 8) {
                            $variableArguments = array_merge($variableArguments, $variableItemsUnique);
                        }
                    }
                    else if ($variableTypeArrayComposition === self::ARRAY_COMPOSITION_OBJECT) {
                        $variableFirstElement = self::getArrayFirstElement($variable);

                        $variableArguments[] = (new \ReflectionClass($variableFirstElement))->isAnonymous()
                            ? self::OBJECT_TYPE_ANONYMOUS
                            : ProcessorTypes::getInstance()->getTypeIndex(get_class($variableFirstElement));
                    }
                    else if ($variableTypeArrayComposition === self::ARRAY_COMPOSITION_RESOURCE) {
                        $variableArguments[] = get_resource_type(self::getArrayFirstElement($variable));
                    }
                }
                break;
            case 'object':
                $variableType      = 'o';
                $variableArguments = (new \ReflectionClass($variable))->isAnonymous()
                    ? [ self::OBJECT_TYPE_ANONYMOUS ]
                    : [ ProcessorTypes::getInstance()->getTypeIndex(get_class($variable)) ];
                break;
            case 'resource':
                $variableType      = 'r';
                $variableArguments = [ get_resource_type($variable) ];
                break;
            default:
                $variableType      = gettype($variable) . '?';
                $variableArguments = [];
        }

        Processor::getInstance()->getFile($filePath)->increaseAnnotationTypeOccurrences($statementIndex, $variableType, $variableArguments);

        return $variable;
    }

    public static function includeFile(string $file): string
    {
        return BurningAutoloader::getInstance()->getProcessedSourcePath($file);
    }

    public static function register(): void
    {
        class_alias(static::class, 'BurningCall');
    }

    private static function getArrayComposition(array $variableItems): string
    {
        $variableFirstElements    = array_slice($variableItems, 0, 1);
        $variableFirstElement     = array_shift($variableFirstElements);
        $variableFirstElementType = gettype($variableFirstElement);

        switch ($variableFirstElementType) {
            case 'string':
                foreach ($variableItems as $variableItem) {
                    if (!is_string($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_STRING;
                break;
            case 'integer':
                foreach ($variableItems as $variableItem) {
                    if (!is_int($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_INTEGER;
                break;
            case 'float':
                foreach ($variableItems as $variableItem) {
                    if (!is_float($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_FLOAT;
                break;
            case 'boolean':
                foreach ($variableItems as $variableItem) {
                    if (!is_bool($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_BOOLEAN;
                break;
            case 'array':
                foreach ($variableItems as $variableItem) {
                    if (!is_array($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_ARRAY;
                break;
            case 'object':
                $variableFirstElementClass   = get_class($variableFirstElement);
                $variableObjectTypeExclusive = true;

                foreach ($variableItems as $variableItem) {
                    if (!is_object($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }

                    if ($variableObjectTypeExclusive && get_class($variableItem) !== $variableFirstElementClass) {
                        $variableObjectTypeExclusive = false;
                    }
                }

                return $variableObjectTypeExclusive
                    ? self::ARRAY_COMPOSITION_OBJECT
                    : self::ARRAY_COMPOSITION_OBJECT_MIXED;
                break;
            case 'NULL':
                foreach ($variableItems as $variableItem) {
                    if ($variableItem !== null) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }
                }

                return self::ARRAY_COMPOSITION_NULL;
                break;
            case 'resource':
                $variableFirstElementResourceType = get_resource_type($variableFirstElement);
                $variableResourceTypeExclusive    = true;

                foreach ($variableItems as $variableItem) {
                    if (!is_resource($variableItem)) {
                        return self::ARRAY_COMPOSITION_MIXED;
                    }

                    if ($variableResourceTypeExclusive && get_resource_type($variableItem) !== $variableFirstElementResourceType) {
                        $variableResourceTypeExclusive = false;
                    }
                }

                return $variableResourceTypeExclusive
                    ? self::ARRAY_COMPOSITION_RESOURCE
                    : self::ARRAY_COMPOSITION_RESOURCE_MIXED;
                break;
        }

        return self::ARRAY_COMPOSITION_MIXED;
    }

    private static function getArrayFirstElement(array $items)
    {
        $itemsElements = array_slice($items, 0, 1);

        return array_shift($itemsElements);
    }

    private static function getStringComposition(string $value): string
    {
        if (!$value) {
            return self::STRING_COMPOSITION_EMPTY;
        }

        if ($value === '0' || $value === '1') {
            return self::STRING_COMPOSITION_BOOLEAN;
        }

        if (ctype_digit($value)) {
            return self::STRING_COMPOSITION_INTEGER;
        }

        if (self::isFloatString($value)) {
            return self::STRING_COMPOSITION_FLOAT;
        }

        return self::STRING_COMPOSITION_GENERIC;
    }

    private static function isEmptyString(string $value): bool
    {
        return !$value && $value !== '0';
    }

    private static function isFloatString(string $value): bool
    {
        return (string) (float) $value === $value;
    }
}
