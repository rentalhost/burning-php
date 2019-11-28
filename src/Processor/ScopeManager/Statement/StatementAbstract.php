<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\ScopeManager\Statement;

use PhpParser\Node;
use Rentalhost\BurningPHP\Processor\ScopeManager\ScopeManager;

abstract class StatementAbstract
{
    abstract public static function apply(ScopeManager $scopeManager, Node $node): bool;
}
