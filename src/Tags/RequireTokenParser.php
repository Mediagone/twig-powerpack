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
    
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();
        
        $nullable = $stream->nextIf('nullable') !== null;
        
        $className = $this->parser->getExpressionParser()->parseExpression();
        if (!$className instanceof ConstantExpression) {
            throw new SyntaxError('The type reference in a "require" statement must be a string (got: '.$className->getAttribute('name').').', $stream->getCurrent()->getLine(), $stream->getSourceContext());
        }
        
        $alias = 'MODEL';
        if ($stream->nextIf('as')) {
            $alias = $stream->expect(Token::NAME_TYPE)->getValue();
        }
        
        $stream->expect(Token::BLOCK_END_TYPE);
        
        return new RequireNode($className->getAttribute('value'), $nullable, $alias, $token->getLine(), $this->getTag());
    }
    
    
    public function getTag(): string
    {
        return 'require';
    }
    
    
    
}
