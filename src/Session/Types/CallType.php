<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types;

use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property string $name
 * @property int    $function
 */
class CallType
    extends AbstractType
{
    /** @var int[] */
    private static $namesIndex = [];

    public static function postProcessing(array $args): array
    {
        $name  = empty($args['name'][0]) ? $args['name'][1] : $args['name'][0] . '::' . $args['name'][1];
        $index = self::$namesIndex[$name] ?? null;

        if ($index === null) {
            self::$namesIndex[$name] = count(self::$namesIndex);

            return parent::postProcessing(array_replace($args, [
                'name' => $name,
                'as'   => self::$namesIndex[$name]
            ]));
        }

        return parent::postProcessing(array_replace($args, [
            'name' => $index
        ]));
    }
}
