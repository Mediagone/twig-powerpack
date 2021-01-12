<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;


final class FooWithArgument
{
    private string $arg;
    
    public function getArg() : string
    {
        return $this->arg;
    }
    
    public function __construct(string $arg)
    {
        $this->arg = $arg;
    }
}
