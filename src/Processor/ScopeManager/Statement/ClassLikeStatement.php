<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class ClassLikeStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            $scopeManager->prefixManager->append(ScopeManager::PREFIX_CLASS . $node->name->toString());

            /** @var Node\Stmt $nodeStmt */
            foreach ($node->stmts as $nodeStmt) {
                MethodStatement::apply($scopeManager, $nodeStmt);
            }

            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
