<?php

namespace market\command\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use market\Main;

class BaseCommand extends Command
{

    const NAME = "";
    const DESCRIPTION = "";
    const USAGE = "";
    const PERMISSION = "";

    protected $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct(static::NAME, static::DESCRIPTION, static::USAGE);
        $this->setPermission(static::PERMISSION);
        $this->plugin = $plugin;
    }
    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(!$this->plugin->isEnabled())
        {
            return false;
        }
        if(!$this->testPermission($sender))
        {
            return false;
        }
        return true;
    }

}