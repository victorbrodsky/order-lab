<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Query;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

//https://gist.github.com/tentacode/3c62aa3db5aa016abcc6

class GroupConcat extends FunctionNode
{
    protected $isDistinct = false;
    protected $expression = null;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('GROUP_CONCAT(%s%s)',
            $this->isDistinct ? 'DISTINCT ' : '',
            $this->expression->dispatch($sqlWalker)
        );
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $lexer = $parser->getLexer();
        if ($lexer->isNextToken(TokenType::T_DISTINCT)) {
            $parser->match(TokenType::T_DISTINCT);
            $this->isDistinct = true;
        }
        $this->expression = $parser->SingleValuedPathExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
