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

    /** @var string */
    private $cachesDirectory;

    /** @var Parser */
    private $parser;

    public function __construct()
    {
        $this->cachesDirectory = BurningConfiguration::getInstance()->getBurningDirectory();

        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, null, [
            'usedAttributes' => [ 'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos' ]
        ]);
    }

    public function process(string $file): string
    {
        $fileHash   = hash('sha256', $file);
        $fileCached = $this->cachesDirectory . '/caches/' . $fileHash . '.php';

        if (is_file($fileCached)) {
            return $fileCached;
        }

        $fileStatements = $this->parser->parse(file_get_contents($file));

        file_put_contents($fileCached, (new PrettyPrinter)->prettyPrintFile($fileStatements), LOCK_EX);

        return $fileCached;
    }
}
