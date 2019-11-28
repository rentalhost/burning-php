<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class ForStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\For_ ||
            $node instanceof Node\Stmt\Foreach_) {
            $node->stmts = ExpressionStatement::applyStatements($scopeManager, $node->stmts);

            return true;
        }

        return false;
    }
}
