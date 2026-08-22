<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;


final class FooWithOptionalArgument
{
    private string $arg;
    
    public function getArg() : string
    {
        return $this->arg;
    }
    
    private string $optionalArg;
    
    public function getOptionalArg() : string
    {
        return $this->optionalArg;
    }
    
    public function __construct(string $arg, string $optionalArg = 'default value')
    {
        $this->arg = $arg;
        $this->optionalArg = $optionalArg;
    }
}
