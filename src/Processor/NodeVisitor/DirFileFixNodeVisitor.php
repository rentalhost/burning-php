<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rentalhost\BurningPHP\Processor\ProcessorFile;

class DirFileFixNodeVisitor
    extends NodeVisitorAbstract
{
    /** @var ProcessorFile|null */
    protected $processorFile;

    public function __construct(ProcessorFile $processorFile)
    {
        $this->processorFile = $processorFile;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Scalar\MagicConst\Dir) {
            return new Node\Scalar\String_(dirname($this->processorFile->sourceOriginalResourcePath));
        }

        if ($node instanceof Node\Scalar\MagicConst\File) {
            return new Node\Scalar\String_($this->processorFile->sourceOriginalResourcePath);
        }

        return parent::enterNode($node);
    }
}
