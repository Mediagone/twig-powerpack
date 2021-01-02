<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Compiler;
use Twig\Node\Node;


final class RequireNode extends Node
{
    //========================================================================================================
    // Properties
    //========================================================================================================

    private string $className;
    
    private bool $nullable;
    
    private string $variableName;
    
    
    
    //========================================================================================================
    // Constructors
    //========================================================================================================
    
    public function __construct(string $className, bool $nullable, string $variableName, int $lineno, string $tag)
    {
        $this->className = $className;
        $this->nullable = $nullable;
        $this->variableName = $variableName;
        parent::__construct([], [], $lineno, $tag);
    }
    
    
    
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        
        $compiler->write("\$templateName = \$this->source->getName();\n");
    
        $compiler->write("if (! array_key_exists('$this->variableName', \$context)) {\n")->indent();
        $compiler->write("throw new \Exception('Missing context variable \"$this->variableName\" required by the template: '.\$templateName);\n")->outdent();
        $compiler->write("}\n");
    
        $compiler->write("if (! \class_exists('$this->className')) {\n")->indent();
        $compiler->write("throw new \Exception('Missing class \"$this->className\" required by the template: '.\$templateName);\n")->outdent();
        $compiler->write("}\n");

        $compiler->write("\$contextVariable = \$context['$this->variableName'];\n");
        if ($this->nullable) {
            $compiler->write("if (! \$contextVariable instanceof $this->className && \$contextVariable !== null) {\n")->indent();
            $compiler->write("\$requiredType = '\"$this->className\" or NULL';\n");
        }
        else {
            $compiler->write("if (! \$contextVariable instanceof $this->className) {\n")->indent();
            $compiler->write("\$requiredType = '\"$this->className\"';\n");
        }
        $compiler->write("\$type = is_object(\$context['$this->variableName']) ? get_class(\$contextVariable) : gettype(\$contextVariable);\n");
        $compiler->write("throw new \Exception('Context variable \"$this->variableName\" must be an instance of '.\$requiredType.' (got: '.\$type.') in '.\$templateName);\n")->outdent();
        $compiler->write("}\n");
    }
    
    
    
}
