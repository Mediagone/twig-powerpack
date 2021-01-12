<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;


final class FooWithTwoArguments
{
    private string $stringArg;
    
    public function getStringArg() : string
    {
        return $this->stringArg;
    }
    
    private array $arrayArg;
    
    public function getArrayArg() : array
    {
        return $this->arrayArg;
    }
    
    public function __construct(string $stringArg, array $arrayArg)
    {
        $this->stringArg = $stringArg;
        $this->arrayArg = $arrayArg;
    }
    
    
}
