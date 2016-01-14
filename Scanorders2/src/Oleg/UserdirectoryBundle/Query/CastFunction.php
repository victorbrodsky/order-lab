<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/14/16
 * Time: 9:40 AM
 */

namespace Oleg\UserdirectoryBundle\Query;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

//http://stackoverflow.com/questions/19096429/error-when-use-custom-dql-function-with-doctrine-and-symfony2

class CastFunction extends FunctionNode {

    public $firstDateExpression = null;
    public $unit = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstDateExpression = $parser->StringPrimary();

        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return sprintf('CAST(%s AS %s)',  $this->firstDateExpression->dispatch($sqlWalker), $this->unit);
    }

} 