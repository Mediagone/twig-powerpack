<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;

use Twig\Compiler;
use Twig\Node\Node;
use function in_array;


final class RequireNode extends Node
{
    //========================================================================================================
    // Properties
    //========================================================================================================
    
    private string $typeName;
    
    private bool $isNullable;
    
    private ?string $subtypeName;
    
    private bool $isSubtypeNullable;
    
    private string $variableName;
    
    
    
    //========================================================================================================
    // Constructors
    //========================================================================================================
    
    public function __construct(string $typeName, bool $isNullable, ?string $subtypeName, bool $isSubtypeNullable, string $variableName, int $lineno, string $tag)
    {
        $this->typeName = $typeName;
        $this->isNullable = $isNullable;
        $this->subtypeName = $subtypeName;
        $this->isSubtypeNullable = $isSubtypeNullable;
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
        
        $this->checkIfVariableIsDefined($compiler);
        $compiler->write("\$contextVariable = \$context['$this->variableName'];\n");
    
        if ($this->typeName === 'array') {
            $this->checkIfPrimitive($compiler, 'array', 'an array');
            $this->checkIfArrayOf($compiler, $this->subtypeName);
        }
        elseif ($this->typeName === 'string') {
            $this->checkIfPrimitive($compiler, 'string', 'a string');
        }
        elseif ($this->typeName === 'bool') {
            $this->checkIfPrimitive($compiler, 'bool', 'a boolean');
        }
        elseif ($this->typeName === 'int') {
            $this->checkIfPrimitive($compiler, 'int', 'an integer');
        }
        elseif ($this->typeName === 'float') {
            $this->checkIfPrimitive($compiler, 'float', 'a float');
        }
        else {
            $this->checkIfClassExists($compiler);
            $this->checkIfClassInstance($compiler);
        }
    }
    
    
    
    //========================================================================================================
    // Private Helpers
    //========================================================================================================
    
    private function checkIfVariableIsDefined(Compiler $compiler) : void
    {
        $compiler->write("// Check if context variable is defined\n");
        $compiler->write("if (! array_key_exists('$this->variableName', \$context)) {\n")->indent();
        $compiler->write("throw new \Exception('Missing context variable \"$this->variableName\" required by the template: '.\$templateName);\n")->outdent();
        $compiler->write("}\n");
    }
    
    
    private function checkIfPrimitive(Compiler $compiler, string $type, string $typeName) : void
    {
        $nullableCondition = $this->isNullable ? ' && $contextVariable !== null' : '';
        $nullableText = $this->isNullable ? ' or NULL' : '';
    
        $compiler->write("// Check if '$type' primitive type\n");
        $compiler->write("if (!is_$type(\$contextVariable)$nullableCondition) {\n")->indent();
        $compiler->write("\$type = is_object(\$contextVariable) ? get_class(\$contextVariable) : gettype(\$contextVariable);\n");
        $compiler->write("throw new \Exception('Context variable \"$this->variableName\" must be $typeName$nullableText (got: '.\$type.') in ');\n")->outdent();
        $compiler->write("}\n");
    }
    
    private function checkIfArrayOf(Compiler $compiler, string $subType) : void
    {
        $nullableCondition = $this->isSubtypeNullable ? ' && $item !== null' : '';
        $nullableText = $this->isSubtypeNullable ? ' or NULL' : '';
        
        if (in_array($subType, ['string', 'bool', 'int', 'float'], true)) {
            $condition = "is_$subType(\$item)$nullableCondition";
        }
        else {
            $condition = "\$item instanceof \\$subType$nullableCondition";
        }
        
        $compiler->write("// Check type of array items\n");
        $compiler->write("array_map(static function(\$item) use(\$contextVariable, \$templateName) {\n");
        $compiler->indent();
        $compiler->write("if (! $condition) {\n");
        $compiler->indent();
        $compiler->write("\$type = is_object(\$item) ? get_class(\$item) : gettype(\$item);\n");
        $compiler->write("throw new \Exception('Context variable \"$this->variableName\" must only contain $subType$nullableText elements (got: '.\$type.') in '.\$templateName);\n");
        $compiler->outdent();
        $compiler->write("}\n");
        $compiler->outdent();
        
        $compiler->write("}, \$contextVariable);\n");
    }
    
    
    private function checkIfClassExists(Compiler $compiler) : void
    {
        $compiler->write("// Check if class exists\n");
        $compiler->write("if (! \class_exists('$this->typeName')) {\n")->indent();
        $compiler->write("throw new \Exception('Missing class \"$this->typeName\" required by the template: '.\$templateName);\n")->outdent();
        $compiler->write("}\n");
    }
    
    
    private function checkIfClassInstance(Compiler $compiler) : void
    {
        $compiler->write("// Check if class instance\n");
        if ($this->isNullable) {
            $compiler->write("if (! \$contextVariable instanceof \\$this->typeName && \$contextVariable !== null) {\n")->indent();
            $compiler->write("\$requiredType = '\"$this->typeName\" or NULL';\n");
        }
        else {
            $compiler->write("if (! \$contextVariable instanceof \\$this->typeName) {\n")->indent();
            $compiler->write("\$requiredType = '\"$this->typeName\"';\n");
        }
        $compiler->write("\$type = is_object(\$contextVariable) ? get_class(\$contextVariable) : gettype(\$contextVariable);\n");
        $compiler->write("throw new \Exception('Context variable \"$this->variableName\" must be an instance of '.\$requiredType.' (got: '.\$type.') in '.\$templateName);\n")->outdent();
        $compiler->write("}\n");
    }
    
    
    
}
