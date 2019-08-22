<?php

namespace market\provider;

use market\provider\providers\PostProvider;
use market\provider\providers\Provider;
use market\provider\providers\TradeProvider;

class ProviderManager
{

    private static $providers = [];

    public static function init($plugin)
    {
        self::register(new TradeProvider($plugin));
        self::register(new PostProvider($plugin));
    }

    public static function close()
    {
        foreach (self::$providers as $provider) $provider->close();
    }

    public static function register(Provider $provider)
    {
        self::$providers[$provider->getId()] = $provider;
    }

    public static function get($id)
    {
        return self::$providers[$id];
    }

}