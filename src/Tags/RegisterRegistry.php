<?php declare(strict_types=1);

namespace Mediagone\Twig\PowerPack\Tags;


final class RegisterRegistry
{
    //========================================================================================================
    // Properties
    //========================================================================================================
    
    private static array $registry = [];
    
    
    
    //========================================================================================================
    // Methods
    //========================================================================================================
    
    public static function register(string $registry, string $data, ?string $key = null) : void
    {
        if (! isset(self::$registry[$registry])) {
            self::$registry[$registry] = [];
        }
        
        if ($key === null) {
            self::$registry[$registry][] = $data;
        }
        else {
            self::$registry[$registry][$data] = $data;
        }
    }
    
    
    public static function read(string $registry) : array
    {
        return self::$registry[$registry] ?? [];
    }
    
    
    public static function clear() : void
    {
        self::$registry = [];
    }
    
    
    
}
