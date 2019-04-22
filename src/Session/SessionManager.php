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

    /** @var bool|resource */
    private $sessionFileHandler;

    public function __construct()
    {
        $burningConfiguration = BurningConfiguration::getInstance();
        $requestTimeFloat     = $_SERVER['REQUEST_TIME_FLOAT'];
        $requestMs            = (int) ($requestTimeFloat * 1000.0);
        $sessionFile          = strtr($burningConfiguration->burningSessionFormat, [ '{%requestMs}' => $requestMs ]);

        $this->sessionFileHandler = fopen(getcwd() . '/' . $burningConfiguration->burningDirectory . '/' . $sessionFile, 'cb');

        ftruncate($this->sessionFileHandler, 0);
        fwrite($this->sessionFileHandler, "[\n]\n");
        fseek($this->sessionFileHandler, 2);

        $initializeType                   = new InitializeType;
        $initializeType->version          = BurningConfiguration::getInstance()->getBurningVersionInt();
        $initializeType->timestamp        = round(microtime(true), 3);
        $initializeType->requestTimestamp = round($requestTimeFloat, 3);

        $this->write($initializeType);

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
        $shutdownType            = new ShutdownType;
        $shutdownType->timestamp = round(microtime(true), 3);

        $this->write($shutdownType);

        fclose($this->sessionFileHandler);
    }

    public function write(Type $type): void
    {
        $prefix = ftell($this->sessionFileHandler) === 2 ? "\t" : ",\n\t";

        fwrite($this->sessionFileHandler, $prefix . json_encode($type) . "\n]\n");
        fseek($this->sessionFileHandler, -3, SEEK_END);
    }
}
