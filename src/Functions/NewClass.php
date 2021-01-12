<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Functions;

use InvalidArgumentException;
use function class_exists;


final class NewClass
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public static function createInstance(string $className, ...$params) : object
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException('Unknown class: '.$className);
        }
        
        return new $className(...$params);
    }
    
    
    
}
