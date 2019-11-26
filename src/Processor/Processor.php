<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class Processor
{
    use SingletonPatternTrait;

    /** @var ProcessorFile[] */
    private $files = [];

    /** @var resource */
    private $filesResource;

    /** @var Parser */
    private $parser;

    /** @var resource */
    private $statementsResource;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, new Lexer([
            'usedAttributes' => [ 'startFilePos', 'endFilePos' ]
        ]));

        $burningConfiguration = BurningConfiguration::getInstance();

        $this->filesResource      = fopen($burningConfiguration->getBurningDirectory() . '/' .
                                          $burningConfiguration->getPathWithSessionMask('FILES'), 'wb');
        $this->statementsResource = fopen($burningConfiguration->getBurningDirectory() . '/' .
                                          $burningConfiguration->getPathWithSessionMask('STATEMENTS'), 'wb');
    }

    public static function stringifyArguments(array $arguments, ?bool $addPrefixSpace = null): ?string
    {
        if (!$arguments) {
            return null;
        }

        foreach ($arguments as &$argument) {
            if (is_string($argument) && strpos($argument, ' ') !== false) {
                $argument = '<' . addcslashes($argument, '>') . '>';
            }
        }

        return ($addPrefixSpace !== false ? ' ' : null) . implode(' ', $arguments);
    }

    public function getFile(string $path): ProcessorFile
    {
        return $this->files[$path];
    }

    public function process(string $filePath): ProcessorFile
    {
        $processorFile = new ProcessorFile($filePath, count($this->files));

        $this->files[$processorFile->phpResourcePath] = $processorFile;

        fwrite($this->filesResource, self::stringifyArguments([ $processorFile->hash, $processorFile->getShortPath() ], false) . "\n");

        $fileStatements = $this->parser->parse(file_get_contents($filePath));

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new ProcessorNodeVisitor($processorFile));

        $modifiedFileStatements = $traverser->traverse($fileStatements);

        $processorFile->writeSource((new PrettyPrinter)->prettyPrintFile($modifiedFileStatements));
        $processorFile->appendStatementsToResource($this->statementsResource);

        return $processorFile;
    }
}
