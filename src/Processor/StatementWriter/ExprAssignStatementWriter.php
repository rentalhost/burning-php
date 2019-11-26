<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\StatementWriter;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorFile;
use Rentalhost\BurningPHP\Processor\StatementWriter\Support\StatementWriterAbstract;

class ExprAssignStatementWriter
    extends StatementWriterAbstract
{
    public static function getStatementArguments(ProcessorFile $processorFile, Node $node): array
    {
        assert($node instanceof Node\Expr\Assign);

        $assignOffset     = $node->getStartFilePos();
        $assignLength     = $node->getEndFilePos() - $assignOffset + 1;
        $varOffset        = $node->var->getStartFilePos();
        $varLength        = $node->var->getEndFilePos() - $varOffset + 1;
        $exprOffset       = $node->expr->getStartFilePos();
        $exprLength       = $node->expr->getEndFilePos() - $exprOffset + 1;
        $signRegionOffset = $varOffset + $varLength;
        $signRegionLength = $exprOffset - $signRegionOffset;
        $signRegion       = $processorFile->getSourceSubstring($signRegionOffset, $signRegionLength);
        $signOffset       = $signRegionOffset + strpos($signRegion, trim($signRegion));

        return [
            $assignOffset . ':' . $assignLength,
            $varLength,
            $signOffset - $assignOffset,
            ($exprOffset - $assignOffset) . ':' . $exprLength
        ];
    }

    public static function getStatementCode(): int
    {
        return 1;
    }
}
