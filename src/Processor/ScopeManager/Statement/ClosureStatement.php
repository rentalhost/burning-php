<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class ClosureStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Expr\Closure) {
            $scopeManager->prefixManager->append(ScopeManager::PREFIX_ANONYMOUS_FUNCTION);
            $scopeManager->variableManager->push();

            $nodeParamsStmts = [];

            foreach ($node->params as $nodeParam) {
                ParamStatement::apply($scopeManager, $nodeParam, $nodeParamsStmts);
            }

            $node->stmts = array_merge($nodeParamsStmts, ExpressionStatement::applyStatements($scopeManager, $node->stmts));

            $scopeManager->variableManager->pop();
            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
