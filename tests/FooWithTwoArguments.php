<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;


final class FooWithTwoArguments
{
    private string $arg1;
    
    public function getArg1() : string
    {
        return $this->arg1;
    }
    
    private string $arg2;
    
    public function getArg2() : string
    {
        return $this->arg2;
    }
    
    public function __construct(string $arg1, string $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}
