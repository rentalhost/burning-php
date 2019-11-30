<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorCallFactory;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;
use Rentalhost\BurningPHP\Processor\StatementWriter\FunctionParameterStatementWriter;

class ExpressionStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\Expression) {
            $nodeExpr = $node->expr;

            if ($nodeExpr instanceof Node\Expr\Assign) {
                $nodeExprVar = $nodeExpr->var;

                if ($nodeExprVar instanceof Node\Expr\Variable && is_string($nodeExprVar->name)) {
                    $variableStatementIndex = $scopeManager->variableManager->getVariable($nodeExprVar->name) ??
                                              $scopeManager->variableManager->registerVariable(
                                                  $nodeExprVar->name,
                                                  FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $nodeExprVar, [
                                                      $scopeManager->prefixManager->toString(ScopeManager::PREFIX_VARIABLE . $nodeExprVar->name)
                                                  ])
                                              );

                    $nodes[] = ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $nodeExpr);

                    return true;
                }
            }
        }

        return false;
    }

    public static function applyStatement(ScopeManager $scopeManager, Node\Stmt $nodeStmt, ?array &$nodes = null): ?Node\Stmt
    {
        if (self::apply($scopeManager, $nodeStmt, $nodes)) {
            return null;
        }

        IfStatement::apply($scopeManager, $nodeStmt) ||
        ForStatement::apply($scopeManager, $nodeStmt);

        return $nodeStmt;
    }

    public static function applyStatements(ScopeManager $scopeManager, array $nodeStmts): array
    {
        $resultNodeStmts = [];

        foreach ($nodeStmts as $nodeStmt) {
            $nodeUpdated = self::applyStatement($scopeManager, $nodeStmt, $resultNodeStmts);

            if ($nodeUpdated) {
                $resultNodeStmts[] = $nodeUpdated;
            }
        }

        return $resultNodeStmts;
    }
}
