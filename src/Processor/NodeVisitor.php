<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class NodeVisitor
    extends NodeVisitorAbstract
{
    /** @var string */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Scalar\MagicConst\Dir) {
            return new Node\Scalar\String_(dirname($this->file));
        }

        return parent::leaveNode($node);
    }
}
