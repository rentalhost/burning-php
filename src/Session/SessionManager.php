<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;
use Rentalhost\BurningPHP\Session\Types\Shutdown\ShutdownType;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class SessionManager
{
    use SingletonPatternTrait;

    /** @var AbstractType[] */
    private $registeredTypes = [];

    public function __construct()
    {
        register_shutdown_function([ $this, 'write' ]);
    }

    public function register(AbstractType $typeInstance): void
    {
        $this->registeredTypes[get_class($typeInstance)] = $typeInstance;
    }

    public function write(): void
    {
        ShutdownType::execute();

        $burningConfiguration = BurningConfiguration::getInstance();
        $sessionFile          = strtr($burningConfiguration->burningSessionFormat, [
            '{%requestMs}' => str_pad(var_export($_SERVER['REQUEST_TIME_FLOAT'], true), 17, '0')
        ]);

        file_put_contents($burningConfiguration->getBurningDirectory() . '/sessions/' . $sessionFile,
            json_encode(array_map(static function (AbstractType $type) {
                return $type->toArray();
            }, $this->registeredTypes), JSON_THROW_ON_ERROR));
    }
}
