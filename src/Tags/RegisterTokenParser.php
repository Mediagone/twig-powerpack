<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TokenStream;
use function pathinfo;


/**
 * Register custom data in global registries.
 */
final class RegisterTokenParser extends AbstractTokenParser
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public function getTag() : string
    {
        return 'register';
    }
    
    
    public function parse(Token $token) : Node
    {
        $stream = $this->parser->getStream();
        
        $unique = $stream->nextIf(Token::NAME_TYPE, 'once') !== null;
        if ($stream->test(Token::STRING_TYPE)) {
            return $this->parseInline($unique, $token, $stream);
        }
        
        return $this->parseBlock($unique, $token, $stream);
    }
    
    
    
    //========================================================================================================
    // Private Helpers
    //========================================================================================================
    
    public function parseInline(bool $unique, Token $token, TokenStream $stream) : Node
    {
        $data = $stream->next()->getValue();
        
        if ($stream->nextIf(Token::OPERATOR_TYPE, 'in')) {
            $registryName = $stream->expect(Token::STRING_TYPE)->getValue();
        }
        else {
            $registryName = pathinfo($data, PATHINFO_EXTENSION);
            if (! $registryName) {
                throw new SyntaxError('Impossible to infer registry name from data, you must define a registry name explicitly.', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        }
        
        $stream->expect(Token::BLOCK_END_TYPE);
        
        $dataNode = new TextNode($data, $stream->getCurrent()->getLine());
        return new RegisterNode($dataNode, $registryName, $unique, $token->getLine(), $this->getTag());
    }
    
    
    public function parseBlock(bool $unique, Token $token, TokenStream $stream) : Node
    {
        $stream->expect(Token::OPERATOR_TYPE, 'in');
        $registryName = $stream->expect(Token::STRING_TYPE)->getValue();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        
        $dataNode = $this->parser->subparse(fn(Token $token) => $token->test(Token::NAME_TYPE, 'endregister'), true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        
        return new RegisterNode($dataNode, $registryName, $unique, $token->getLine(), $this->getTag());
    }
    
    
    
}
