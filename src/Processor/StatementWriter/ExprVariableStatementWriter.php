<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\StatementWriter;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorFile;
use Rentalhost\BurningPHP\Processor\StatementWriter\Support\StatementWriterAbstract;

class ExprVariableStatementWriter
    extends StatementWriterAbstract
{
    public static function getStatementArguments(ProcessorFile $processorFile, Node $node): array
    {
        assert($node instanceof Node\Expr\Variable);

        $nodeOffset = $node->getStartFilePos();
        $nodeLength = $node->getEndFilePos() - $nodeOffset + 1;

        return [ $nodeOffset . ':' . $nodeLength ];
    }

    public static function getStatementCode(): int
    {
        return 0;
    }
}
