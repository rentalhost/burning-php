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

    /** @var BuilderFactory|null */
    private static $builderFactory;

    /**
     * @param string|AbstractType $typeClassname
     */
    public static function call(string $typeClassname, ?array $args = null): void
    {
        $typeClassname::getInstance()->call($args);
    }

    public static function create(string $typeClassname, ?array $args = null, ?Node $node = null): Node\Stmt\Expression
    {
        if (!self::$builderFactory) {
            self::$builderFactory = new BuilderFactory;
        }

        if ($node instanceof Node\Stmt) {
            $args           = (array) $args;
            $args['offset'] = $node->getStartFilePos();
            $args['length'] = $node->getEndFilePos() - $args['offset'];
        }

        return new Node\Stmt\Expression(new Node\Expr\FuncCall(
            new Node\Name(BurningConfiguration::getInstance()->sessionProxyFactoryFunction),
            [
                new Node\Expr\ClassConstFetch(new Node\Name('\\' . $typeClassname), 'class'),
                self::$builderFactory->val($args)
            ]
        ));
    }

    public static function register(): void
    {
        eval(sprintf('function %s(string $typeClassname, array $args) { return $typeClassname::getInstance()->call($args); }',
            BurningConfiguration::getInstance()->sessionProxyFactoryFunction));
    }
}
