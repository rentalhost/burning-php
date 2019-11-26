<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningConfiguration;
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

    /** @var int[] */
    private static $annotationsTypesOccurrences = [];

    /** @var resource|null */
    private $callsResource;

    /**
     * @param mixed $variable
     * @return mixed
     */
    public static function annotateType(string $filePath, int $statementIndex, $variable)
    {
        switch (gettype($variable)) {
            case 'string':
                $variableType      = 's';
                $variableArguments = [];

                $variableTypeStringLength = strlen($variable);

                if ($variableTypeStringLength) {
                    $variableArguments = [ $variableTypeStringLength ];

                    if ($variableTypeStringLength <= 255) {
                        $variableArguments[] = ProcessorStrings::getInstance()->getStringIndex($variable);
                    }
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

                            $variableArguments = array_merge($variableArguments, array_map([ $processorStrings, 'getStringIndex' ], $variableItemsUnique));
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

        self::increaseAnnotationTypeOccurrences($filePath, $statementIndex, $variableType, $variableArguments);

        return $variable;
    }

    public static function register(): void
    {
        class_alias(static::class, 'BurningCall');

        register_shutdown_function([ static::class, 'writeAnnotationsTypesOccurrences' ]);
    }

    public static function writeAnnotationsTypesOccurrences(): void
    {
        $processorCall = self::getInstance();

        foreach (self::$annotationsTypesOccurrences as $occurrenceFormat => $occurrenceCount) {
            $processorCall->writeCallRaw(strtr($occurrenceFormat, [ '%u' => $occurrenceCount ]));
        }
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

        if ((string) (float) $value === $value) {
            return self::STRING_COMPOSITION_FLOAT;
        }

        return self::STRING_COMPOSITION_GENERIC;
    }

    private static function increaseAnnotationTypeOccurrences(string $filePath, int $statementIndex, string $variableType, array $variableArguments): void
    {
        $processorString = Processor::stringifyArguments(array_merge([
            Processor::getInstance()->getFile($filePath)->index,
            $statementIndex,
            '%u',
            $variableType,
        ], $variableArguments), false);

        if (!array_key_exists($processorString, self::$annotationsTypesOccurrences)) {
            self::$annotationsTypesOccurrences[$processorString] = 1;

            return;
        }

        self::$annotationsTypesOccurrences[$processorString]++;
    }

    public function initialize(): void
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->callsResource = fopen($burningConfiguration->getBurningDirectory() . '/' .
                                     $burningConfiguration->getPathWithSessionMask('CALLS'), 'wb');
    }

    public function writeCall(int $fileIndex, int $statementIndex, ...$arguments): void
    {
        $this->writeCallRaw($fileIndex . ' ' . $statementIndex . Processor::stringifyArguments($arguments));
    }

    public function writeCallRaw(string $content): void
    {
        fwrite($this->callsResource, $content . "\n");
    }
}
