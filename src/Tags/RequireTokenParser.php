<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;


/**
 * Ensure that a typed context variable is provided to the template.
 *
 *    {% require 'App\\UI\\ViewModels\Foo' as FOO %}
 */
final class RequireTokenParser extends AbstractTokenParser
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public function parse(Token $token) : Node
    {
        $stream = $this->parser->getStream();
        
        $isNullable = $stream->nextIf('nullable') !== null;
        
        if ($stream->nextIf('array') !== null) {
            if (! $stream->nextIf('of')) {
                throw new SyntaxError('Missing "of" keyword is required after "require array" expression', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
            
            $isSubtypeNullable = $stream->nextIf('nullable') !== null;
            $subtype = $this->parser->getExpressionParser()->parseExpression();
            if (!$subtype instanceof ConstantExpression) {
                throw new SyntaxError('The type reference in a "require" statement must be a string (got: ' . $subtype->getAttribute('name') . ').', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
    
            $typeName = 'array';
            $subtypeName = $subtype->getAttribute('value');
        }
        else {
            $type = $this->parser->getExpressionParser()->parseExpression();
            if (!$type instanceof ConstantExpression) {
                throw new SyntaxError('The type reference in a "require" statement must be a string (got: ' . $type->getAttribute('name') . ').', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
            
            $typeName = $type->getAttribute('value');
            $subtypeName = null;
            $isSubtypeNullable = false;
        }
        
        $alias = $stream->nextIf('as') ? $stream->expect(Token::NAME_TYPE)->getValue() : 'MODEL';
        
        $stream->expect(Token::BLOCK_END_TYPE);
        
        return new RequireNode($typeName, $isNullable, $subtypeName, $isSubtypeNullable, $alias, $token->getLine(), $this->getTag());
    }
    
    
    public function getTag(): string
    {
        return 'require';
    }
    
    
    
}
