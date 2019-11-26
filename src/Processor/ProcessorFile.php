<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningConfiguration;

class ProcessorFile
{
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

    /** @var resource */
    public $statementsResource;

    /** @var string */
    public $statementsResourcePath;

    /** @var string */
    private $path;

    /** @var int */
    private $statementsCount;

    public function __construct(string $path, int $index)
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->index    = $index;
        $this->path     = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $this->hash     = strtoupper(substr(hash_file('sha256', $path), 0, 8));
        $this->hashFile = $this->hash . '_' . $this->getBasename();

        $resourcesPath = str_replace('/', DIRECTORY_SEPARATOR, $burningConfiguration->getBurningDirectory() . '/caches/' . $this->hashFile);

        $this->phpResourcePath        = $resourcesPath . '.php';
        $this->phpResource            = fopen($this->phpResourcePath, 'wb');
        $this->statementsResourcePath = $resourcesPath . '.php.STATEMENTS';
        $this->statementsResource     = fopen($this->statementsResourcePath, 'w+b');
        $this->statementsCount        = 0;
        $this->sourceResource         = fopen($path, 'rb');
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
        return preg_replace('/\..+?$/', null, basename($this->path));
    }

    public function getShortPath(): string
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        return substr($this->path, strlen($burningConfiguration->currentWorkingDir) + 1);
    }

    public function getSourceSubstring(int $offset, int $length): string
    {
        fseek($this->sourceResource, $offset);

        return fread($this->sourceResource, $length);
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
