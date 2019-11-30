<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ProcessorCallFactory;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;
use Rentalhost\BurningPHP\Processor\StatementWriter\FunctionParameterStatementWriter;

class ParamStatement
    extends StatementAbstract
{
    public static function apply(ScopeManager $scopeManager, Node $node, ?array &$nodes = null): bool
    {
        if ($node instanceof Node\Param) {
            $variableStatementIndex = FunctionParameterStatementWriter::writeStatement($scopeManager->processorFile, $node->var, [
                $scopeManager->prefixManager->toString(ScopeManager::PREFIX_PARAMETER_VARIABLE . $node->var->name)
            ]);

            $nodes[] = ProcessorCallFactory::createVariableAnnotationCall($variableStatementIndex, $node->var);

            $scopeManager->variableManager->registerVariable($node->var->name, $variableStatementIndex);

            return true;
        }

        return false;
    }
}
