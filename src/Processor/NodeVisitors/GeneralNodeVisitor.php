<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\NodeVisitors;

use PhpParser\Node;

class GeneralNodeVisitor
    extends AbstractNodeVisitor
{
    /** @var string */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            return static::traverseWithNodeVisitor($node, new ClassMethodNodeVisitor($node));
        }

        if ($node instanceof Node\Scalar\MagicConst\Dir) {
            return new Node\Scalar\String_(dirname($this->file));
        }

        if ($node instanceof Node\Scalar\MagicConst\File) {
            return new Node\Scalar\String_($this->file);
        }

        return parent::enterNode($node);
    }
}
