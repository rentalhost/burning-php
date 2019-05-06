<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\NodeVisitors;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractNodeVisitor
    extends NodeVisitorAbstract
{
    protected static function traverseWithNodeVisitor(Node $node, NodeVisitorAbstract $nodeVisitor): Node
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor($nodeVisitor);
        $traverser->traverse([ $node ]);

        return $node;
    }
}
