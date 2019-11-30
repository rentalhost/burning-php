<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorCallFactory;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;
use Rentalhost\BurningPHP\Processor\StatementWriter\FunctionParameterStatementWriter;

class ForStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\Foreach_) {
            $node->stmts = ExpressionStatement::applyStatements($scopeManager, $node->stmts);

            self::processForParameter($scopeManager, $node, $node->keyVar);
            self::processForParameter($scopeManager, $node, $node->valueVar);

            return true;
        }

        if ($node instanceof Node\Stmt\For_) {
            $node->stmts = ExpressionStatement::applyStatements($scopeManager, $node->stmts);

            return true;
        }

        return false;
    }

    private static function processForParameter(ScopeManager $scopeManager, Node $nodeParent, ?Node $node): void
    {
        if ($node instanceof Node\Expr\Variable) {
            $variableStatementIndex = $scopeManager->variableManager->getVariable($node->name) ??
                                      $scopeManager->variableManager->registerVariable(
                                          $node->name,
                                          FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $node, [
                                              $scopeManager->prefixManager->toString(ScopeManager::PREFIX_VARIABLE . $node->name)
                                          ])
                                      );

            array_unshift($nodeParent->stmts, ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $node));
        }
    }
}
