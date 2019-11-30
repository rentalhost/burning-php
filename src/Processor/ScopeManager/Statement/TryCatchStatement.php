<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class TryCatchStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\TryCatch) {
            $node->stmts = ExpressionStatement::applyStatements($scopeManager, $node->stmts);

            foreach ($node->catches as $nodeCatch) {
                $nodeCatch->stmts = ExpressionStatement::applyStatements($scopeManager, $nodeCatch->stmts);
            }

            if ($node->finally) {
                $node->finally = ExpressionStatement::applyStatement($scopeManager, $node->finally);
            }

            return true;
        }

        return false;
    }
}
