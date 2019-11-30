<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager;

use PhpParser\Node\Stmt;
use Rentalhost\BurningPHP\Processor\PrefixManager\PrefixManager;
use Rentalhost\BurningPHP\Processor\ProcessorFile;
use Rentalhost\BurningPHP\Processor\ScopeManager\Statement\NamespaceStatement;
use Rentalhost\BurningPHP\Processor\VariableManager\VariableManager;

class ScopeManager
{
    public const
        PREFIX_NAMESPACE = 'n',
        PREFIX_CLASS = 'c',
        PREFIX_METHOD = 'm',
        PREFIX_ANONYMOUS_FUNCTION = 'a',
        PREFIX_PARAMETER_VARIABLE = 'p',
        PREFIX_VARIABLE = 'v';

    /** @var PrefixManager */
    public $prefixManager;

    /** @var ProcessorFile */
    public $processorFile;

    /** @var VariableManager */
    public $variableManager;

    public function __construct(ProcessorFile $processorFile)
    {
        $this->processorFile   = $processorFile;
        $this->prefixManager   = new PrefixManager;
        $this->variableManager = new VariableManager;
    }

    public function processStatements(array $statements): void
    {
        /** @var Stmt $stmt */
        foreach ($statements as $stmt) {
            NamespaceStatement::apply($this, $stmt);
        }
    }
}
