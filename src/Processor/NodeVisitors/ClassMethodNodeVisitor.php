<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\NodeVisitors;

use PhpParser\Node;
use Rentalhost\BurningPHP\Session\SessionProxyFactory;
use Rentalhost\BurningPHP\Session\Types\Call\CallType;

class ClassMethodNodeVisitor
    extends AbstractNodeVisitor
{
    public function __construct(Node\Stmt\ClassMethod $classMethodStatement)
    {
        if ($classMethodStatement->stmts) {
            array_unshift($classMethodStatement->stmts, SessionProxyFactory::create(
                CallType::class,
                [
                    'class'    => new Node\Scalar\MagicConst\Class_,
                    'function' => new Node\Scalar\MagicConst\Function_
                ],
                $classMethodStatement
            ));
        }
    }
}
