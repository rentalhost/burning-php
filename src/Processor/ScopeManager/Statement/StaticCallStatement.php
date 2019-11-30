<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class StaticCallStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\Expression) {
            $nodeExpr = $node->expr;

            if ($nodeExpr instanceof Node\Expr\StaticCall) {
                foreach ($nodeExpr->args as $nodeExprArg) {
                    ClosureStatement::apply($scopeManager, $nodeExprArg->value);
                }

                return true;
            }
        }

        return false;
    }
}
