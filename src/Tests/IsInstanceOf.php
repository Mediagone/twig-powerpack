<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tests;

use function is_a;


final class IsInstanceOf
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public static function instanceOf(?object $object, string $className) : bool
    {
        return is_a($object, $className);
    }
    
    
    
}
