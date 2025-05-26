<?php

namespace App\DoctrineExtensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;

class RandFunction extends FunctionNode
{
    public $seed = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER); // Match RAND
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        // Check if a seed is provided
        if (!$parser->getLexer()->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
            $this->seed = $parser->ArithmeticPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'RAND(' . ($this->seed ? $this->seed->dispatch($sqlWalker) : '') . ')';
    }
}
