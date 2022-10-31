<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Lexer;

/**
 * usage: match_against(field, :search)
 */
class MatchAgainst extends FunctionNode
{
    public $field = null;
    public $param = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->StateFieldPathExpression();
        $parser->match(Lexer::T_COMMA);
        $this->param = $parser->InputParameter();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'match(' .
            $this->field->dispatch($sqlWalker) .
            ') against(' .
            $this->param->dispatch($sqlWalker) .
            ')';
    }
}
