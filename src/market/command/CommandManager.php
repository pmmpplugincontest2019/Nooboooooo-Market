<?php

namespace market\command;

use market\command\commands\MarketCommand;
use market\Main;

class CommandManager
{

    public static function init(Main $plugin)
    {
        $map = $plugin->getServer()->getCommandMap();
        $map->register("market", new MarketCommand($plugin));
    }

}