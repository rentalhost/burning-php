<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\ShutdownType;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class SessionManager
{
    use SingletonPatternTrait;

    /** @var BurningConfiguration */
    private $burningConfiguration;

    /** @var bool|resource */
    private $sessionFileHandler;

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

        register_shutdown_function([ $this, 'shutdown' ]);
    }

    public function shutdown(): void
    {
        ShutdownType::write([ 'clean' => true ]);

        fclose($this->sessionFileHandler);
    }

    public function write(array $type): void
    {
        $prefix = ftell($this->sessionFileHandler) === 2 ? "\t" : ",\n\t";

        fwrite($this->sessionFileHandler, $prefix . json_encode($type) . "\n]\n");
        fseek($this->sessionFileHandler, -3, SEEK_END);

        if ($this->burningConfiguration->forceWriteShutdownObject) {
            $shutdownObjectContent = ",\n\t" . json_encode(ShutdownType::generate([ 'clean' => 'false' ])) . "\n]\n";

            fwrite($this->sessionFileHandler, $shutdownObjectContent);
            fseek($this->sessionFileHandler, -strlen($shutdownObjectContent), SEEK_END);
        }
    }
}
