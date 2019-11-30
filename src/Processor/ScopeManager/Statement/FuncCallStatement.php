<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class FuncCallStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\Expression) {
            $nodeExpr = $node->expr;

            if ($nodeExpr instanceof Node\Expr\StaticCall ||
                $nodeExpr instanceof Node\Expr\FuncCall) {
                foreach ($nodeExpr->args as $nodeExprArg) {
                    ClosureStatement::apply($scopeManager, $nodeExprArg->value);
                }

                return true;
            }
        }

        if ($node instanceof Node\Expr\FuncCall) {
            foreach ($node->args as $nodeArg) {
                ClosureStatement::apply($scopeManager, $nodeArg->value);
            }

            return true;
        }

        return false;
    }
}
