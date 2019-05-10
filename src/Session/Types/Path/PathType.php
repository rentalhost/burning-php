<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Path;

use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property PathReference[] $files
 */
class PathType
    extends AbstractType
{
    public function __construct()
    {
        $this->files = [];
    }

    public function registerClass(string $path, ?string $class = null): void
    {
        if (!array_key_exists($path, $this->files)) {
            $pathReference            = new PathReference;
            $pathReference->class     = $class;
            $pathReference->timestamp = microtime(true);

            $this->files[$path] = $pathReference;
        }
    }
}
