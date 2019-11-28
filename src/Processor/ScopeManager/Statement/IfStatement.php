<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class IfStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\If_ ||
            $node instanceof Node\Stmt\Else_ ||
            $node instanceof Node\Stmt\ElseIf_) {
            $node->stmts = ExpressionStatement::applyStatements($scopeManager, $node->stmts);

            return true;
        }

        return false;
    }
}
