<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\AbstractType;
use Rentalhost\BurningPHP\Session\Types\InitializeType;
use Rentalhost\BurningPHP\Session\Types\ShutdownType;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class SessionManager
{
    use SingletonPatternTrait;

    /** @var BurningConfiguration */
    private $burningConfiguration;

    /** @var bool|resource */
    private $sessionFileHandler;

    /** @var ShutdownType */
    private $shutdownObjectInstance;

    public function __construct()
    {
        $this->burningConfiguration = BurningConfiguration::getInstance();

        $sessionFile = strtr($this->burningConfiguration->burningSessionFormat, [
            '{%requestMs}' => str_pad(var_export($_SERVER['REQUEST_TIME_FLOAT'], true), 17, '0')
        ]);

        $this->sessionFileHandler = fopen($this->burningConfiguration->getBurningDirectory() . '/sessions/' . $sessionFile, 'cb');

        ftruncate($this->sessionFileHandler, 0);
        fwrite($this->sessionFileHandler, "[\n]\n");
        fseek($this->sessionFileHandler, 2);

        $shutdownObjectInstance        = new ShutdownType;
        $shutdownObjectInstance->clean = false;

        $this->shutdownObjectInstance = $shutdownObjectInstance;

        $this->write(new InitializeType);

        register_shutdown_function([ $this, 'shutdown' ]);
    }

    public function shutdown(): void
    {
        $shutdownObjectInstance        = $this->shutdownObjectInstance;
        $shutdownObjectInstance->clean = true;
        $shutdownObjectInstance->write();

        fclose($this->sessionFileHandler);
    }

    public function write(AbstractType $type): void
    {
        $prefix = ftell($this->sessionFileHandler) === 2 ? "\t" : ",\n\t";

        fwrite($this->sessionFileHandler, $prefix . json_encode($type) . "\n]\n");
        fseek($this->sessionFileHandler, -3, SEEK_END);

        if ($this->burningConfiguration->forceWriteShutdownObject) {
            $shutdownObjectInstance = $this->shutdownObjectInstance;
            $shutdownObjectContent  = ",\n\t" . json_encode($shutdownObjectInstance) . "\n]\n";

            fwrite($this->sessionFileHandler, $shutdownObjectContent);
            fseek($this->sessionFileHandler, -strlen($shutdownObjectContent), SEEK_END);
        }
    }
}
