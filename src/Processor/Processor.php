<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Processor\NodeVisitors\GeneralNodeVisitor;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class Processor
{
    use SingletonPatternTrait;

    /** @var ProcessorFile[] */
    private $files = [];

    /** @var resource */
    private $headerResource;

    /** @var Parser */
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, new Lexer([
            'usedAttributes' => [ 'startFilePos', 'endFilePos' ]
        ]));

        $this->headerResource = fopen(BurningConfiguration::getInstance()->getBurningDirectory() . '/FILES', 'wb');
    }

    public function process(string $filepath): string
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $processorFile = new ProcessorFile($filepath);

        $this->files[] = $processorFile;

        fwrite($this->headerResource, sprintf("h<%s> p<%s>\n",
            $processorFile->hash,
            $processorFile->getShortPath()));

        $fileHash   = $processorFile->hash . '_' . $processorFile->getBasename();
        $fileCached = $burningConfiguration->getBurningDirectory() . '/caches/' . $fileHash . '.php';

        $fileStatements = $this->parser->parse(file_get_contents($filepath));

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new GeneralNodeVisitor($filepath));

        $modifiedFileStatements = $traverser->traverse($fileStatements);

        file_put_contents($fileCached, (new PrettyPrinter)->prettyPrintFile($modifiedFileStatements), LOCK_EX);

        return $fileCached;
    }
}
