<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\BuilderFactory;
use PhpParser\Node;

class ProcessorCallFactory
{
    /** @var BuilderFactory|null */
    private static $builderFactory;

    public static function createMethodCall(string $method, int $statementIndex, Node\Stmt\Expression $expression): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(
            new Node\Name\FullyQualified('BurningCall'),
            new Node\Name($method),
            [
                new Node\Scalar\MagicConst\File,
                self::getBuilderFactory()->val($statementIndex),
                $expression->expr
            ]
        ));
    }

    private static function getBuilderFactory(): BuilderFactory
    {
        if (!self::$builderFactory) {
            self::$builderFactory = new BuilderFactory;
        }

        return self::$builderFactory;
    }
}
