<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\InitializeType;
use Rentalhost\BurningPHP\Session\Types\ShutdownType;
use Rentalhost\BurningPHP\Session\Types\Type;

class SessionManager
{
    /** @var self */
    private static $instance;

    /** @var BurningConfiguration */
    private $burningConfigurationInstance;

    /** @var bool|resource */
    private $sessionFileHandler;

    /** @var ShutdownType */
    private $shutdownObjectInstance;

    public function __construct()
    {
        $this->burningConfiguration = BurningConfiguration::getInstance();

        $requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $sessionFile      = strtr($this->burningConfiguration->burningSessionFormat, [
            '{%requestMs}' => str_pad(var_export($requestTimeFloat, true), 17, '0')
        ]);

        $this->sessionFileHandler = fopen(getcwd() . '/' . $this->burningConfiguration->burningDirectory . '/' . $sessionFile, 'cb');

        ftruncate($this->sessionFileHandler, 0);
        fwrite($this->sessionFileHandler, "[\n]\n");
        fseek($this->sessionFileHandler, 2);

        $shutdownObjectInstance        = new ShutdownType;
        $shutdownObjectInstance->clean = false;

        $this->shutdownObjectInstance = $shutdownObjectInstance;

        $initializeObjectInstance                   = new InitializeType;
        $initializeObjectInstance->version          = BurningConfiguration::getInstance()->getBurningVersionInt();
        $initializeObjectInstance->requestTimestamp = $requestTimeFloat;

        $this->write($initializeObjectInstance);

        register_shutdown_function([ $this, 'shutdown' ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::makeOutputDirectory();

        $instance = new self;

        return self::$instance = $instance;
    }

    private static function makeOutputDirectory(): void
    {
        $currentWorkingDir    = getcwd();
        $burningConfiguration = BurningConfiguration::getInstance();
        $burningDirectory     = $currentWorkingDir . '/' . $burningConfiguration->burningDirectory;

        if (!is_dir($burningDirectory)) {
            mkdir($burningDirectory, fileperms($currentWorkingDir));
        }
    }

    public function shutdown(): void
    {
        $shutdownObjectInstance        = $this->shutdownObjectInstance;
        $shutdownObjectInstance->clean = true;

        $this->write($shutdownObjectInstance);

        fclose($this->sessionFileHandler);
    }

    public function write(Type $type): void
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
