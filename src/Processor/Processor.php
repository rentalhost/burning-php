<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Support\SingletonPattern;

class Processor
{
    use SingletonPattern;

    /** @var Parser */
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, null, [
            'usedAttributes' => [ 'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos' ]
        ]);
    }

    public function process(string $file): string
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $fileHash   = hash('sha256', $file);
        $fileCached = $burningConfiguration->getBurningDirectory() . '/caches/' . $fileHash . '.php';

        if (!$burningConfiguration->disableCache && is_file($fileCached)) {
            return $fileCached;
        }

        $fileStatements = $this->parser->parse(file_get_contents($file));

        $fileStatements = $this->parser->parse(file_get_contents($file));

        file_put_contents($fileCached, (new PrettyPrinter)->prettyPrintFile($fileStatements), LOCK_EX);

        return $fileCached;
    }
}
