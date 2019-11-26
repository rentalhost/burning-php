<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\StatementWriter\Support;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorFile;

abstract class StatementWriterAbstract
    implements StatementWriterInterface
{
    public static function writeStatement(ProcessorFile $processorFile, Node $node): int
    {
        assert(in_array(StatementWriterInterface::class, class_implements(static::class), true));

        return $processorFile->writeStatement(static::getStatementCode(), ... static::getStatementArguments($processorFile, $node));
    }
}
