<?php

namespace market\form\forms;

use market\provider\providers\PostProvider;
use market\provider\providers\TradeProvider;
use pocketmine\item\Item;
use pocketmine\Player;

use market\form\forms\Form;

class MarketEditForm extends Form
{

    private $index = [];

    private $id;

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
			    $this->index = TradeProvider::get()->getIndex($this->player->getName());
                $buttons = [];
			    foreach ($this->index as $tradeId)
                {
                    $tradeData = TradeProvider::get()->getTrade($tradeId);
                    $buttons[] = ['text' => "§l出§f|§8 {$tradeData["exhibit"]["name"]} {$tradeData["exhibit"]["amount"]}個\n求§f|§8{$tradeData["request"]["name"]} {$tradeData["request"]["amount"]}個"];
                    $cache[] = 2;
                }

			    if($buttons === [])
                {
                    $this->sendModal("§lMarket §r > §8出品管理", "何も出品していません", "戻る", "閉じる", 4, 0);
                    return;
                }

                $data = [
                    'type'    => 'form',
                    'title'   => '§lMarket',
                    'content' => "編集したい出品設定を選択してください",
                    'buttons' => $buttons
                ];
				break;

            case 2:
                $id = $this->index[$this->lastData];
                $trade = TradeProvider::get()->getTrade($id);

                $this->id = $id;

                $this->sendModal("§lMarket §r > §8出品管理", "§a出)§f : {$trade["exhibit"]["name"]} (ID {$trade["exhibit"]["id"]}:{$trade["exhibit"]["damage"]}) {$trade["exhibit"]["amount"]}個\n§a求)§f : {$trade["request"]["name"]} (ID {$trade["request"]["id"]}:{$trade["request"]["damage"]}) {$trade["request"]["amount"]}個", "§c出品取り消し", "戻る", 3, 1);
                break;

            case 3:
                $trade = TradeProvider::get()->getTrade($this->id);
                PostProvider::get()->addItem($this->player->getName(), Item::get($trade["exhibit"]["id"], $trade["exhibit"]["damage"], $trade["exhibit"]["amount"]));
                TradeProvider::get()->delete($this->id);
                $this->sendModal("§lMarket §r > §8出品管理", "出品を取り消しました\nアイテムはアイテムポストに返却されました", "戻る", "閉じる", 4, 0);
                break;

            case 4:
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
