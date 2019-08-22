<?php

namespace market;

use market\provider\ProviderManager;
use pocketmine\plugin\PluginBase;
use market\command\CommandManager;
use market\form\FormManager;

class Main extends PluginBase
{

    private $commandManager;

    private $formManager;

    public function onEnable()
    {
        CommandManager::init($this);
        FormManager::init($this);
        ProviderManager::init($this);
    }

    public function onDisable()
    {
        ProviderManager::close();
    }

}