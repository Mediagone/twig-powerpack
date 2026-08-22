<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenStream;
use Twig\TokenParser\AbstractTokenParser;


/**
 * Ensure that a typed context variable is provided to the template.
 *
 *    {% expect 'App\\UI\\ViewModels\Foo' as FOO %}
 */
final class ExpectTokenParser extends AbstractTokenParser
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public function parse(Token $token) : Node
    {
        $stream = $this->parser->getStream();
        
        // The "optional" keyword always comes first, right after the tag name, so that every declaration
        // reads in the same order: optionality, then nullability, then type.
        $isOptional = $stream->nextIf(Token::NAME_TYPE, 'optional') !== null;
        $isNullable = $stream->nextIf(Token::NAME_TYPE, 'nullable') !== null;
        $this->checkOptionalKeywordPosition($stream);
        
        if ($stream->nextIf('array') !== null) {
            if (! $stream->nextIf('of')) {
                throw new SyntaxError('Missing "of" keyword is required after "expect array" expression', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
            
            $isSubtypeNullable = $stream->nextIf('nullable') !== null;
            $this->checkOptionalKeywordPosition($stream);
            $subtype = $this->parser->getExpressionParser()->parseExpression();
            if (!$subtype instanceof ConstantExpression) {
                throw new SyntaxError('The type reference in a "expect" statement must be a string (got: ' . $subtype->getAttribute('name') . ').', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
    
            $typeName = 'array';
            $subtypeName = $subtype->getAttribute('value');
        }
        else {
            $type = $this->parser->getExpressionParser()->parseExpression();
            if (!$type instanceof ConstantExpression) {
                throw new SyntaxError('The type reference in a "expect" statement must be a string (got: ' . $type->getAttribute('name') . ').', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
            
            $typeName = $type->getAttribute('value');
            $subtypeName = null;
            $isSubtypeNullable = false;
        }
        
        $alias = $stream->nextIf('as') ? $stream->expect(Token::NAME_TYPE)->getValue() : 'MODEL';
        
        $stream->expect(Token::BLOCK_END_TYPE);
        
        return new ExpectNode($typeName, $isNullable, $isOptional, $subtypeName, $isSubtypeNullable, $alias, $token->getLine(), $this->getTag());
    }
    
    
    public function getTag(): string
    {
        return 'expect';
    }
    
    
    
    //========================================================================================================
    // Private Helpers
    //========================================================================================================
    
    /**
     * Fails explicitly when "optional" is written anywhere else than right after the tag name.
     */
    private function checkOptionalKeywordPosition(TokenStream $stream) : void
    {
        if ($stream->test(Token::NAME_TYPE, 'optional')) {
            throw new SyntaxError('The "optional" keyword must be placed right after the tag name (eg. {% expect optional nullable \'Foo\' as FOO %}).', $stream->getCurrent()->getLine(), $stream->getSourceContext());
        }
    }
    
    
    
}
