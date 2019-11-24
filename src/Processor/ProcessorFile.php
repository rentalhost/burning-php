<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningConfiguration;

class ProcessorFile
{
    /** @var string */
    public $hash;

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = str_replace('\\', '/', $path);
        $this->hash = strtoupper(substr(hash_file('sha256', $path), 0, 8));
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
}
