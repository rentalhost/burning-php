<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\NodeVisitors;

use PhpParser\Node;
use Rentalhost\BurningPHP\Session\SessionProxyFactory;
use Rentalhost\BurningPHP\Session\Types\CallType;

class ClassMethodNodeVisitor
    extends AbstractNodeVisitor
{
    public function __construct(Node\Stmt\ClassMethod $classMethodStatement)
    {
        if ($classMethodStatement->stmts) {
            array_unshift($classMethodStatement->stmts, SessionProxyFactory::createWithNode(
                CallType::class,
                $classMethodStatement, [
                    'name' => new Node\Expr\Array_([ new Node\Scalar\MagicConst\Class_, new Node\Scalar\MagicConst\Function_ ])
                ]
            ));
        }
    }
}
