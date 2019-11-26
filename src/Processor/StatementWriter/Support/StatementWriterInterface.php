<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\StatementWriter\Support;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorFile;

interface StatementWriterInterface
{
    public static function getStatementArguments(ProcessorFile $processorFile, Node $node): array;

    public static function getStatementCode(): int;
}
