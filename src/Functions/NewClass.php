<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Functions;

use InvalidArgumentException;
use function array_keys;
use function class_exists;
use function count;
use function is_array;
use function is_string;


final class NewClass
{
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    /**
     * Instantiates the given class, spreading its parameters as named arguments when a single non-empty
     * associative array is supplied (every key is a string), so that `new('Foo', {Label: 'x', Href: '/y'})`
     * from a Twig template behaves like PHP named arguments. Every other case (no argument, several
     * arguments, an empty array, or an array with at least one numeric key) keeps the previous behaviour:
     * the parameters are passed positionally.
     */
    public static function createInstance(string $className, ...$params) : object
    {
        self::assertClassExists($className);
        
        if (self::isSingleAssociativeArray($params)) {
            return new $className(...$params[0]);
        }
        
        return new $className(...$params);
    }
    
    
    /**
     * Instantiates the given class, always passing its parameters positionally — the escape hatch for a
     * class whose first constructor parameter is itself expected to be an associative array, which
     * `createInstance()` would otherwise spread as named arguments.
     */
    public static function createInstancePositional(string $className, ...$params) : object
    {
        self::assertClassExists($className);
        
        return new $className(...$params);
    }
    
    
    
    //========================================================================================================
    // Helpers
    //========================================================================================================
    
    private static function assertClassExists(string $className) : void
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException('Unknown class: '.$className);
        }
    }
    
    
    private static function isSingleAssociativeArray(array $params) : bool
    {
        if (count($params) !== 1 || ! is_array($params[0]) || $params[0] === []) {
            return false;
        }
        
        foreach (array_keys($params[0]) as $key) {
            if (! is_string($key)) {
                return false;
            }
        }
        
        return true;
    }
    
    
    
}
