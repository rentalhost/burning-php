<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Node;
use Rentalhost\BurningPHP\BurningConfiguration;

class ProcessorFile
{
    /** @var resource */
    public $callsResource;

    /** @var string */
    public $callsResourcePath;

    /** @var string */
    public $hash;

    /** @var int */
    public $index;

    /** @var resource */
    public $phpResource;

    /** @var string */
    public $phpResourcePath;

    /** @var resource */
    public $sourceResource;

    /** @var string */
    public $sourceResourcePath;

    /** @var resource */
    public $statementsResource;

    /** @var string */
    public $statementsResourcePath;

    /** @var int[] */
    private $annotationsTypesOccurrences = [];

    /** @var int */
    private $statementsCount;

    public function __construct(string $path, int $index)
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->sourceResource     = fopen($path, 'rb');
        $this->sourceResourcePath = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->index    = $index;
        $this->hash     = strtoupper(substr(hash_file('sha256', $path), 0, 8));
        $this->hashFile = $this->hash . '_' . $this->getBasename();

        $resourcesPath = str_replace('/', DIRECTORY_SEPARATOR, $burningConfiguration->getBurningDirectory() . '/caches/' . $this->hashFile);

        $this->callsResourcePath      = $burningConfiguration->getBurningDirectory() . '/' .
                                        $burningConfiguration->getPathWithSessionMask($this->hash . '.CALLS');
        $this->phpResourcePath        = $resourcesPath . '.php';
        $this->phpResource            = fopen($this->phpResourcePath, 'wb');
        $this->statementsResourcePath = $resourcesPath . '.php.STATEMENTS';
        $this->statementsResource     = fopen($this->statementsResourcePath, 'w+b');
        $this->statementsCount        = 0;
    }

    public function __destruct()
    {
        $this->writeAnnotationsTypesOccurrences();
    }

    public function appendStatementsToResource($statementsResource): void
    {
        if (!fstat($this->statementsResource)['size']) {
            return;
        }

        fseek($this->statementsResource, 0);

        while (($resourceBuffer = fgets($this->statementsResource)) !== false) {
            fwrite($statementsResource, $this->index . ' ' . $resourceBuffer);
        }
    }

    public function getBasename(): string
    {
        return preg_replace('/\..+?$/', null, basename($this->sourceResourcePath));
    }

    public function getShortPath(): string
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        return substr($this->sourceResourcePath, strlen($burningConfiguration->currentWorkingDir) + 1);
    }

    public function getSourceSubstring(int $offset, int $length): string
    {
        fseek($this->sourceResource, $offset);

        return fread($this->sourceResource, $length);
    }

    public function getSourceSubstringFromNode(Node $node): string
    {
        return $this->getSourceSubstring($node->getStartFilePos(), $node->getEndFilePos());
    }

    public function increaseAnnotationTypeOccurrences(int $statementIndex, string $variableType, array $variableArguments): void
    {
        $processorString = Processor::stringifyArguments(array_merge([
            $this->index,
            $statementIndex,
            '%u',
            $variableType,
        ], $variableArguments), false);

        if (!array_key_exists($processorString, $this->annotationsTypesOccurrences)) {
            $this->annotationsTypesOccurrences[$processorString] = 1;

            return;
        }

        $this->annotationsTypesOccurrences[$processorString]++;
    }

    public function writeAnnotationsTypesOccurrences(): void
    {
        if ($this->annotationsTypesOccurrences) {
            $this->callsResource = fopen($this->callsResourcePath, 'wb');

            foreach ($this->annotationsTypesOccurrences as $occurrenceFormat => $occurrenceCount) {
                $this->writeCallRaw(strtr($occurrenceFormat, [ '%u' => $occurrenceCount ]));
            }
        }
    }

    public function writeCall(int $statementIndex, ...$arguments): void
    {
        $this->writeCallRaw($statementIndex . Processor::stringifyArguments($arguments));
    }

    public function writeCallRaw(string $content): void
    {
        fwrite($this->callsResource, $content . "\n");
    }

    public function writeSource(string $contents): void
    {
        fwrite($this->phpResource, $contents);
        fclose($this->phpResource);
    }

    public function writeStatement(int $statementType, ...$statementArguments): int
    {
        fwrite($this->statementsResource, $statementType . Processor::stringifyArguments($statementArguments) . "\n");

        return $this->statementsCount++;
    }
}
