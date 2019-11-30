<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorCallFactory;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;
use Rentalhost\BurningPHP\Processor\StatementWriter\FunctionParameterStatementWriter;

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

            foreach ($node->uses as $nodeUse) {
                $nodeUseVar = $nodeUse->var;

                if ($nodeUseVar instanceof Node\Expr\Variable) {
                    if ($nodeUse->byRef) {
                        $variableStatementIndex = $scopeManager->variableManager->importVariable($nodeUseVar->name) ??
                                                  $scopeManager->variableManager->registerVariable(
                                                      $nodeUseVar->name,
                                                      FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $nodeUseVar, [
                                                          $scopeManager->prefixManager->toString(ScopeManager::PREFIX_VARIABLE . $nodeUseVar->name)
                                                      ])
                                                  );
                    }
                    else {
                        $variableStatementIndex = $scopeManager->variableManager->registerVariable(
                            $nodeUseVar->name,
                            FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $nodeUseVar, [
                                $scopeManager->prefixManager->toString(ScopeManager::PREFIX_VARIABLE . $nodeUseVar->name)
                            ])
                        );
                    }

                    $nodeParamsStmts[] = ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $nodeUseVar);
                }
            }

            $node->stmts = array_merge($nodeParamsStmts, ExpressionStatement::applyStatements($scopeManager, $node->stmts));

            $scopeManager->variableManager->pop();
            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
