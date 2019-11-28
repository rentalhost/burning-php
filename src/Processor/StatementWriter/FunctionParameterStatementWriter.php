<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\StatementWriter;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorFile;
use Rentalhost\BurningPHP\Processor\StatementWriter\Support\StatementWriterAbstract;

class FunctionParameterStatementWriter
    extends StatementWriterAbstract
{
    public static function getStatementArguments(ProcessorFile $processorFile, Node $node): array
    {
        assert($node instanceof Node\Expr\Variable);

        return [ $node->getStartFilePos() ];
    }

    public static function getStatementCode(): int
    {
        return 0;
    }
}
