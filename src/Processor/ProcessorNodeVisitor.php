<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rentalhost\BurningPHP\Processor\StatementWriter\ExprAssignStatementWriter;

class ProcessorNodeVisitor
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
            return new Node\Scalar\String_(dirname($this->processorFile->sourceResourcePath));
        }

        if ($node instanceof Node\Scalar\MagicConst\File) {
            return new Node\Scalar\String_($this->processorFile->sourceResourcePath);
        }

        return parent::enterNode($node);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\If_ ||
            $node instanceof Node\Stmt\Else_ ||
            $node instanceof Node\Stmt\ElseIf_ ||
            $node instanceof Node\Stmt\Foreach_ ||
            $node instanceof Node\Stmt\For_ ||
            $node instanceof Node\Stmt\Do_ ||
            $node instanceof Node\Stmt\While_ ||
            $node instanceof Node\Stmt\Case_ ||
            $node instanceof Node\Stmt\ClassMethod ||
            $node instanceof Node\Expr\Closure ||
            $node instanceof Node\Stmt\Function_ ||
            $node instanceof Node\Stmt\TryCatch ||
            $node instanceof Node\Stmt\Catch_ ||
            $node instanceof Node\Stmt\Finally_) {
            if ($node->stmts) {
                $nodeStmts = [];

                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Node\Stmt\Expression && $stmt->expr instanceof Node\Expr\Assign) {
                        $statementIndex = ExprAssignStatementWriter::writeStatement($this->processorFile, $stmt->expr);

                        $nodeStmts[] = ProcessorCallFactory::createMethodCall('annotateType', $statementIndex, $stmt->expr);

                        continue;
                    }

                    $nodeStmts[] = $stmt;
                }

                $node->stmts = $nodeStmts;
            }
        }

        return parent::leaveNode($node);
    }
}
