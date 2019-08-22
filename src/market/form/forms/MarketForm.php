<?php


namespace market\form\forms;

use pocketmine\Server;
use market\provider\providers\PostProvider;
use market\provider\providers\TradeProvider;
use pocketmine\item\Item;
use pocketmine\Player;

class MarketForm extends Form
{

    private $page = 1;

    private $trades = [];

    private $trade = [];

    private static $providers = [];

    public function send(int $id)
    {
        $cache = [];
        $data = [];

        switch ($id)
        {
            case 1:
                $buttons = [];
                $buttons[] = $this->page > 1 ? ['text' => '§l前のページへ'] : ['text' => '§l§7前のページへ'];
                $cache[] = 2;

                $trades = TradeProvider::get()->getTrades($this->page * 5 - 1, ($this->page - 1) * 5);
                $this->trades = $trades;

                foreach ($this->trades as $tradeData) {
                    $buttons[] = ['text' => "§l出§f|§8 {$tradeData["exhibit"]["name"]} {$tradeData["exhibit"]["amount"]}個\n求§f|§8{$tradeData["request"]["name"]} {$tradeData["request"]["amount"]}個"];
                    $cache[] = 11;
                }

                $buttons[] = TradeProvider::get()->getTrades($this->page * 5 , $this->page * 5) === [] ? ['text' => '§l§7次のページへ'] : ['text' => '§l次のページへ'];
                $cache[] = 3;

                $data = [
                    'type'    => 'form',
                    'title'   => "§lMarket §r > §8取引",
                    'content' => "マーケットへようこそ！！\nここではアイテムを物々交換できます！！\n\n",
                    'buttons' => $buttons
                ];
                break;

            case 2:
                if($this->page > 1) $this->page--;
                $this->send(1);
                return;

            case 3:
                if(TradeProvider::get()->getTrades($this->page * 5 , $this->page * 5) !== []) $this->page++;
                $this->send(1);
                return;

            case 11:
                $trade = $this->trades[$this->lastData - 1];
                $this->trade = $trade;
                $label1 = $this->player->getInventory()->contains(Item::get($trade["request"]["id"], $trade["request"]["damage"], $trade["request"]["amount"])) ? "交換する" : "§7交換する";
                $this->sendModal("§lMarket §r > §8取引", "§a出)§f : {$trade["exhibit"]["name"]} (ID {$trade["exhibit"]["id"]}:{$trade["exhibit"]["damage"]}) {$trade["exhibit"]["amount"]}個\n§a求)§f : {$trade["request"]["name"]} (ID {$trade["request"]["id"]}:{$trade["request"]["damage"]}) {$trade["request"]["amount"]}個\n\n\nこの交換を行う場合は「交換する」ボタンを押してください", $label1, "戻る", 12, 1);
                break;

            case 12:
                if($this->trade["exhibitor"] === $this->player->getName())
                {
                    $this->sendModal("§lMarket §r > §8取引", "自分の出品したアイテムです", "戻る", "閉じる", 1, 0);
                    return;
                }
                $request = Item::get($this->trade["request"]["id"], $this->trade["request"]["damage"], $this->trade["request"]["amount"]);
                if($this->player->getInventory()->contains($request))
                {
                    $exhibit = Item::get($this->trade["exhibit"]["id"], $this->trade["exhibit"]["damage"], $this->trade["exhibit"]["amount"]);
                    $this->player->getInventory()->removeItem($request);
                    PostProvider::get()->addItem($this->player->getName(), $exhibit);
                    PostProvider::get()->addItem($this->trade["exhibitor"], $request);
                    TradeProvider::get()->delete($this->trade["id"]);
                    $this->sendModal("§lMarket §r > §8取引", "取引が完了しました\nメニュー画面の「アイテムポスト」よりアイテムを受け取ることができます", "戻る", "閉じる", 13, 0);

                    $exhibitor = Server::getInstance()->getPlayer($this->trade["exhibitor"]);
                    if($exhibitor instanceof Player) $exhibitor->sendMessage("§e§lMarket>>§r§a{$this->player->getName()}§fさんがあなたの出品した§a{$this->trade["exhibit"]["name"]}§fを§a{$this->trade["request"]["name"]}§fと交換しました！");
                }
                else
                {
                    $this->sendModal("§lMarket §r > §8取引", "必要なアイテムを持っていません", "戻る", "閉じる", 1, 0);
                }
                return;

            case 13:
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