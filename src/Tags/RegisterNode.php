<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Compiler;
use Twig\Node\Node;


final class RegisterNode extends Node
{
    //========================================================================================================
    // Properties
    //========================================================================================================
    
    private string $registryName;
    
    private bool $unique;
    
    private Node $body;
    
    private ?int $priority;
    
    
    
    //========================================================================================================
    // Constructors
    //========================================================================================================
    
    public function __construct(Node $body, string $registryName, bool $unique, ?int $priority, int $lineno, string $tag)
    {
        $this->registryName = $registryName;
        $this->unique = $unique;
        $this->body = $body;
        $this->priority = $priority;
        
        parent::__construct([], [], $lineno, $tag);
    }
    
    
    
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public function compile(Compiler $compiler)
    {
        $compiler->write("ob_start();\n");
        $compiler->subcompile($this->body);
        $compiler->write('$content = ob_get_clean();' . PHP_EOL);
        if ($this->unique) {
            $compiler->write('\\' . RegisterRegistry::class . "::register('$this->registryName', \$content, \$content, ".($this->priority ?? 'null').");" . PHP_EOL);
        }
        else {
            $compiler->write('\\' . RegisterRegistry::class . "::register('$this->registryName', \$content, null, ".($this->priority ?? 'null').");" . PHP_EOL);
        }
    }
    
    
    
}
