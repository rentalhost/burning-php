<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorCallFactory;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;
use Rentalhost\BurningPHP\Processor\StatementWriter\FunctionParameterStatementWriter;

class MethodStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            $scopeManager->prefixManager->append(ScopeManager::PREFIX_METHOD . $node->name->toString());
            $scopeManager->variableManager->push();

            if ($node->stmts) {
                $nodeParamsStmts = [];

                /** @var Node\Param $nodeParam */
                foreach ($node->params as $nodeParam) {
                    $variableStatementIndex = FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $nodeParam->var, [
                        $scopeManager->prefixManager->toString(ScopeManager::PREFIX_PARAMETER_VARIABLE . $nodeParam->var->name)
                    ]);

                    $nodeParamsStmts[] = ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $nodeParam->var);

                    $scopeManager->variableManager->registerVariable($nodeParam->var->name, $variableStatementIndex);
                }

                $node->stmts = array_merge($nodeParamsStmts, ExpressionStatement::applyStatements($scopeManager, $node->stmts));
            }

            $scopeManager->variableManager->pop();
            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
