<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager;

use PhpParser\Node\Stmt;
use Rentalhost\BurningPHP\Processor\PrefixManager\PrefixManager;
use Rentalhost\BurningPHP\Processor\ProcessorFile;
use Rentalhost\BurningPHP\Processor\ScopeManager\Statement\NamespaceStatement;

class ScopeManager
{
    public const
        PREFIX_NAMESPACE = 'n',
        PREFIX_CLASS = 'c',
        PREFIX_METHOD = 'm',
        PREFIX_PARAMETER = 'p';

    /** @var PrefixManager */
    public $prefixManager;

    /** @var ProcessorFile */
    public $processorFile;

    public function __construct(ProcessorFile $processorFile)
    {
        $this->processorFile = $processorFile;
        $this->prefixManager = new PrefixManager;
    }

    public function processStatements(array $statements): void
    {
        /** @var Stmt $stmt */
        foreach ($statements as $stmt) {
            NamespaceStatement::apply($this, $stmt);
        }
    }
}
