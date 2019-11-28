<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

class NamespaceStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node): bool
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $scopeManager->prefixManager->append(ScopeManager::PREFIX_NAMESPACE . $node->name->toString());

            foreach ($node->stmts as $nodeStmt) {
                ClassStatement::apply($scopeManager, $nodeStmt);
            }

            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
