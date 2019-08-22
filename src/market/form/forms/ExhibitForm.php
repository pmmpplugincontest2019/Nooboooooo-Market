<?php

namespace market\form\forms;

use pocketmine\Server;
use market\provider\providers\TradeProvider;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\Player;

class ExhibitForm extends Form
{

    private $itemList = [];

    private $exhibitItem = [
        "id" => 0,
        "damage" => 0,
        "amount" => 0,
        "name" => ""
    ];

    private $requestItem = [
        "id" => 0,
        "damage" => 0,
        "amount" => 0,
        "name" => ""
    ];

    public function __construct($player)
    {
        parent::__construct($player);
    }

    public function send(int $id)
    {

        $cache = [];
        $data = [];

        switch($id)
        {
            case 1:
                $itemArray = [];
                $count = 0;
                foreach ($this->player->getInventory()->getContents() as $item)
                {
                    if(!$item->hasEnchantments() && !$item->hasCustomName())
                    {
                        $id = $item->getId();
                        $damage = $item->getDamage();
                        $amount = $item->getCount();
                        $key = $id . ':' . $damage;
                        if(isset($itemArray[$key]))
                        {
                            $itemArray[$key]['amount'] += $amount;
                        }
                        else
                        {
                            $itemArray[$key] = [
                                'id' => $id,
                                'damage' => $damage,
                                'amount' => $amount,
                                'name' => $item->getName(),
                                'number' => $count
                            ];
                        }
                        $count++;
                    }
                }

                $this->itemList = array_column($itemArray, null, 'count');

                $content = [];
                $content[] = ["type" => "dropdown", "text" => "出品するアイテムを選んでください\n(エンチャント済み、または名前付きのアイテムは出品できません)", "options" => array_column($itemArray, 'name')];
                $data = [
                    'type'    => 'custom_form',
                    'title'   => '§lMarket §r > §8出品',
                    'content' => $content
                ];
                $cache = [2];
                break;

            case 2:
                $itemData = $this->itemList[$this->lastData[0]];
                $this->exhibitItem["id"] = $itemData["id"];
                $this->exhibitItem["damage"] = $itemData["damage"];
                $this->exhibitItem["name"] = $itemData["name"];
                $content = [];
                $content[] = ["type" => "label", "text" => "§a出品するアイテム§f : {$itemData["name"]} (ID {$itemData["id"]}:{$itemData["damage"]})\n"];
                $content[] = ["type" => "slider", "text" => "出品する個数を選択してください", "min" => 1, "max" => $itemData["amount"], "default" => $itemData["amount"]];
                $data = [
                    'type'    => 'custom_form',
                    'title'   => '§lMarket §r > §8出品',
                    'content' => $content
                ];
                $cache = [3];
                break;

            case 3:
                if($this->exhibitItem["amount"] === 0) $this->exhibitItem["amount"] = $this->lastData[1];
                $content = [];
                $content[] = ["type" => "label", "text" => "§a出品するアイテム§f : {$this->exhibitItem["name"]} (ID {$this->exhibitItem["id"]}:{$this->exhibitItem["damage"]}) {$this->exhibitItem["amount"]}個"];
                $content[] = ["type" => "input", "text" => "要求するアイテムのIDを入力してください", "placeholder" => "IDを入力", "default" => "1"];
                $content[] = ["type" => "input", "text" => "要求するアイテムのダメージ値を入力してください", "placeholder" => "ダメージ値を入力", "default" => "0"];
                $content[] = ["type" => "input", "text" => "要求するアイテムの個数を入力してください", "placeholder" => "個数を入力", "default" => "1"];
                $data = [
                    'type'    => 'custom_form',
                    'title'   => '§lMarket §r > §8出品',
                    'content' => $content
                ];
                $cache = [4];
                break;

            case 4:
                if(!is_numeric($this->lastData[1]))
                {
                    $this->sendModal("§lMarket §r > §8出品", "§cError>>§fIDは数値を入力してください", "戻る", "閉じる", 3, 0);
                    return;
                }
                if(!is_numeric($this->lastData[2]))
                {
                    $this->sendModal("§lMarket §r > §8出品", "§cError>>§fダメージ値は数値を入力してください", "戻る", "閉じる", 3, 0);
                    return;
                }
                if(!is_numeric($this->lastData[3]))
                {
                    $this->sendModal("§lMarket §r > §8出品", "§cError>>§f個数は数値を入力してください", "戻る", "閉じる", 3, 0);
                    return;
                }

                $id = (int) $this->lastData[1];
                $damage = (int) $this->lastData[2];
                $amount = (int) $this->lastData[3];
                $request = Item::get((int) $this->lastData[1], (int) $this->lastData[2], (int) $this->lastData[3]);
                $name = $request->getName();
                if($id === 0 || $name === "Unknown")
                {
                    $this->sendModal("§lMarket §r > §8出品", "存在しないアイテムです", "戻る", "閉じる", 3, 0);
                    return;
                }

                $this->requestItem = [
                    "id" => $id,
                    "damage" => $damage,
                    "amount" => $amount,
                    "name" => $name
                ];
                $this->sendModal("§lMarket §r > §8出品", "§a出品するアイテム§f : {$this->exhibitItem["name"]} (ID {$this->exhibitItem["id"]}:{$this->exhibitItem["damage"]}) {$this->exhibitItem["amount"]}個\n§a要求するアイテム§f : {$this->requestItem["name"]} (ID {$this->requestItem["id"]}:{$this->requestItem["damage"]}) {$this->requestItem["amount"]}個\n\n以上の内容で出品しますか?", "出品する", "閉じる", 5, 0);
                return;

            case 5:
                $exhibit = Item::get($this->exhibitItem["id"], $this->exhibitItem["damage"], $this->exhibitItem["amount"]);
                if(!$this->player->getInventory()->contains($exhibit))
                {
                    $this->sendModal("§lMarket §r > §8出品", "§cError>>§f出品するアイテムを持っていません", "閉じる", "閉じる", 0, 0);
                    return;
                }

                TradeProvider::get()->make($this->player->getName(), $exhibit, Item::get($this->requestItem["id"], $this->requestItem["damage"], $this->requestItem["amount"]));
                $this->player->getInventory()->removeItem($exhibit);
                $this->sendModal("§lMarket §r > §8出品", "出品が完了しました", "戻る", "閉じる", 6, 0);
                Server::getInstance()->broadcastMessage("§e§lMarket>>§r§a{$this->player->getName()}§fさんが§a{$this->exhibitItem["name"]}§fを§a{$this->exhibitItem["amount"]}個§f出品しました！");
                return;

            case 6:
                $this->close();
                MenuForm::create($this->player);
                return;

            default:
                $this->close();
                return;
        }

        if($cache !== []){
            $this->lastSendData = $data;
            $this->cache = $cache;
            $this->show($id, $data);
        }
    }

}