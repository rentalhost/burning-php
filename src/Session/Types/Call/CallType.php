<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Call;

use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property CallReference[] $functions
 */
class CallType
    extends AbstractType
{
    public function __construct()
    {
        $this->functions = [];
    }

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
            $callReferenceInit         = new CallReference;
            $callReferenceInit->offset = $args['offset'];
            $callReferenceInit->length = $args['length'];

            $this->functions[$functionName] = $callReferenceInit;
        }

        $callFlow         = new CallFlow;
        $callFlow->starts = microtime(true);

        $callReference = $this->functions[$functionName];

        assert($callReference instanceof CallReference);

        $callReference->callFlows[] = $callFlow;
    }
}
