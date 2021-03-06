<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Node;
use Rentalhost\BurningPHP\BurningConfiguration;

class ProcessorFile
{
    public const
        CALL_TYPE_ANNOTATION = 0;

    /** @var resource */
    public $callsResource;

    /** @var string */
    public $callsResourcePath;

    /** @var string */
    public $hash;

    /** @var int */
    public $index;

    /** @var string */
    public $sessionStatementResourcePath;

    /** @var resource */
    public $sourceOriginalResource;

    /** @var string */
    public $sourceOriginalResourcePath;

    /** @var resource */
    public $sourceProcessedResource;

    /** @var string */
    public $sourceProcessedResourcePath;

    /** @var resource */
    public $sourceStatementsResource;

    /** @var string */
    public $sourceStatementsResourcePath;

    /** @var int[] */
    private $annotationsTypesOccurrences = [];

    /** @var int */
    private $statementsCount;

    public function __construct(string $path, int $index)
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->sourceOriginalResource     = fopen($path, 'rb');
        $this->sourceOriginalResourcePath = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->index    = $index;
        $this->hash     = strtoupper(substr(hash_file('sha256', $path), 0, 8));
        $this->hashFile = $this->hash . '_' . $burningConfiguration->burningSourceHash . '_' . $burningConfiguration->getHash() . '_' . $this->getBasename();

        $cachePath     = str_replace('/', DIRECTORY_SEPARATOR, $burningConfiguration->getBurningDirectory() . '/cache/');
        $resourcesPath = $cachePath . $this->hashFile;

        $this->callsResourcePath            = $burningConfiguration->getBurningDirectory() . '/' .
                                              $burningConfiguration->getPathWithSessionMask($this->hash . '.CALLS');
        $this->sourceProcessedResourcePath  = $resourcesPath . '.php';
        $this->sourceProcessedResource      = fopen($this->sourceProcessedResourcePath, 'wb');
        $this->sessionStatementResourcePath = $burningConfiguration->getBurningDirectory() . '/' .
                                              $burningConfiguration->getPathWithSessionMask($this->hash . '.STATEMENTS');
        $this->sourceStatementsResourcePath = $resourcesPath . '.php.STATEMENTS';
        $this->sourceStatementsResource     = fopen($this->sourceStatementsResourcePath, 'w+b');
        $this->statementsCount              = 0;

        copy($path, $cachePath . $this->hash . '_' . $this->getBasename() . '.php');
    }

    public function __destruct()
    {
        $this->writeAnnotationsTypesOccurrences();
        $this->copySourceStatementsToSession();
    }

    public function copySourceStatementsToSession(): void
    {
        if ($this->statementsCount) {
            copy($this->sourceStatementsResourcePath, $this->sessionStatementResourcePath);
        }
    }

    public function getBasename(): string
    {
        return preg_replace('/\..+?$/', null, basename($this->sourceOriginalResourcePath));
    }

    public function getShortPath(): string
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        return substr($this->sourceOriginalResourcePath, strlen($burningConfiguration->currentWorkingDir) + 1);
    }

    public function getSourceSubstring(int $offset, int $length): string
    {
        fseek($this->sourceOriginalResource, $offset);

        return fread($this->sourceOriginalResource, $length);
    }

    public function getSourceSubstringFromNode(Node $node): string
    {
        return $this->getSourceSubstring($node->getStartFilePos(), $node->getEndFilePos());
    }

    public function increaseAnnotationTypeOccurrences(int $statementIndex, string $variableType, array $variableArguments): void
    {
        $processorString = Processor::stringifyArguments(array_merge([
            $statementIndex,
            self::CALL_TYPE_ANNOTATION,
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
        fwrite($this->sourceProcessedResource, $contents);
        fclose($this->sourceProcessedResource);
    }

    public function writeStatement(int $statementType, array $statementArguments): int
    {
        fwrite($this->sourceStatementsResource, $statementType . Processor::stringifyArguments($statementArguments) . "\n");

        return $this->statementsCount++;
    }
}
