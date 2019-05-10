<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Call;

use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

class CallType
    extends AbstractType
{
    public $functions = [];

    private static function getFunctionName(array $args): string
    {
        return empty($args['class'])
            ? $args['function']
            : "{$args['class']}::{$args['function']}";
    }

    public function call(?array $args = null)
    {
        assert(is_array($args));

        $functionName = self::getFunctionName($args);

        if (!array_key_exists($functionName, $this->functions)) {
            $this->functions[$functionName] = new CallReference;
        }

        $callFlow         = new CallFlow;
        $callFlow->starts = microtime(true);

        /** @var CallReference $callReference */
        $callReference              = $this->functions[$functionName];
        $callReference->callFlows[] = $callFlow;
    }
}
