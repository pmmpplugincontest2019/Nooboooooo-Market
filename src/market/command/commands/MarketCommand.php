<?php

namespace market\command\commands;

use market\form\forms\MenuForm;
use pocketmine\command\CommandSender;
use market\command\commands\BaseCommand;
use pocketmine\Player;

class MarketCommand extends BaseCommand
{

    const NAME = "market";
    const DESCRIPTION = "マーケットを開く";
    const USAGE = "";
    const PERMISSION = "market.command.market";


    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }

        if(!$sender instanceof Player)
        {
            $sender->sendMessage("[Market]§cサーバー内で実行してください§f");
            return true;
        }

        MenuForm::create($sender);
        return true;
    }

}