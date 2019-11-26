<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rentalhost\BurningPHP\Processor\StatementWriter\ExprVariableStatementWriter;

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
        if ($node instanceof Node\Stmt\ClassMethod && $node->stmts) {
            $variables = new VariablesBag;
            $nodeStmts = [];

            if ($node->params) {
                foreach ($node->params as $nodeParam) {
                    assert($nodeParam instanceof Node\Param);

                    $variables->registerVariable($nodeParam->var->name, ExprVariableStatementWriter::writeStatement($this->processorFile, $nodeParam->var));
                }
            }

            foreach ($node->stmts as $nodeStmt) {
                if ($nodeStmt instanceof Node\Stmt\Expression &&
                    $nodeStmt->expr instanceof Node\Expr\Assign &&
                    $nodeStmt->expr->var instanceof Node\Expr\Variable) {
                    $statementIndex = $variables->getVariable($nodeStmt->expr->var->name) ??
                                      $variables->registerVariable($nodeStmt->expr->var->name,
                                          ExprVariableStatementWriter::writeStatement($this->processorFile, $nodeStmt->expr->var));

                    $nodeStmts[] = [
                        $nodeStmt,
                        ProcessorCallFactory::createMethodCall('annotateType', $statementIndex, $nodeStmt->expr->var)
                    ];

                    continue;
                }

                $nodeStmts[] = [ $nodeStmt ];
            }

            $node->stmts = array_merge(... $nodeStmts);

            if ($node->params) {
                foreach ($node->params as $nodeParam) {
                    assert($nodeParam instanceof Node\Param);

                    array_unshift($node->stmts, ProcessorCallFactory::createMethodCall('annotateType', $variables[$nodeParam->var->name], $nodeParam->var));
                }
            }
        }

        return parent::leaveNode($node);
    }
}
