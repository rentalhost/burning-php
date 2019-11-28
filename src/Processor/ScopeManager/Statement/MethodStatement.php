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
    public static function apply(ScopeManager $scopeManager, Node $node): bool
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            $scopeManager->prefixManager->append(ScopeManager::PREFIX_METHOD . $node->name->toString());

            if ($node->stmts) {
                $nodeCallStmts = [];

                /** @var Node\Param $nodeParam */
                foreach ($node->params as $nodeParam) {
                    $variableStatementIndex = FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $nodeParam->var, [
                        $scopeManager->prefixManager->toString(ScopeManager::PREFIX_PARAMETER . $nodeParam->var->name)
                    ]);

                    $nodeCallStmts[] = ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $nodeParam->var);
                }

                array_unshift($node->stmts, ... $nodeCallStmts);
            }

            $scopeManager->prefixManager->pop();

            return true;
        }

        return false;
    }
}
