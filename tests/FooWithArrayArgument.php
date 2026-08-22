<?php declare(strict_types=1);

namespace Tests\Mediagone\Twig\PowerPack;


final class FooWithArrayArgument
{
    private array $arg;
    
    public function getArg() : array
    {
        return $this->arg;
    }
    
    public function __construct(array $arg)
    {
        $this->arg = $arg;
    }
}
