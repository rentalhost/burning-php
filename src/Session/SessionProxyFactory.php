<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class SessionProxyFactory
{
    use SingletonPatternTrait;

    /** @var BuilderFactory */
    private static $builderFactory;

    public static function create(string $typeClassname, ?array $args = null): Node\Stmt\Expression
    {
        if (!self::$builderFactory) {
            self::$builderFactory = new BuilderFactory;
        }

        $parameters = array_replace($args, [ 'type' => new Node\Expr\ClassConstFetch(new Node\Name('\\' . $typeClassname), 'class') ]);

        return new Node\Stmt\Expression(new Node\Expr\FuncCall(
            new Node\Name(BurningConfiguration::getInstance()->sessionProxyFactoryFunction),
            [ self::$builderFactory->val($parameters) ]
        ));
    }

    /**
     * @todo calculates $offset and $length based on $node.
     */
    public static function createWithNode(string $typeClassname, Node $node, ?array $args = null): Node\Stmt\Expression
    {
        return self::create($typeClassname, array_replace($args ?? [], [
            'offset' => $node->getStartTokenPos(),
            'length' => $node->getEndTokenPos()
        ]));
    }

    public static function register(): void
    {
        eval(sprintf('function %s(array $args) { return %s::write($args); }',
            BurningConfiguration::getInstance()->sessionProxyFactoryFunction,
            static::class));
    }

    public static function write(array $declaration): void
    {
        /** @var AbstractType $typeClassname */
        $typeClassname = $declaration['type'];

        SessionManager::getInstance()->write(array_replace(
            array_filter($typeClassname::postProcessing($declaration), [ self::class, 'filterNull' ]),
            [
                'type'      => $typeClassname::getType(),
                'timestamp' => microtime(true)
            ]
        ));
    }

    private static function filterNull($value): bool
    {
        return $value !== null;
    }
}
