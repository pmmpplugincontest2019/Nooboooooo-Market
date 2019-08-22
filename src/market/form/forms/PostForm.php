<?php

namespace market\form\forms;

use market\provider\providers\PostProvider;
use pocketmine\item\Item;
use pocketmine\Player;

use market\form\forms\Form;

class PostForm extends Form
{

    private $items = [];

    private $message;

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
				$buttons = [];
				$this->items = [];

                foreach (PostProvider::get()->getItemData($this->player->getName()) as $key => $amount) {
                    $itemData = explode(":", $key);
                    $item = Item::get($itemData[0], $itemData[1], $amount);
                    $this->items[] = $item;
                    $buttons[] = ['text' => "§l{$item->getName()}§r\n§8個数 : {$amount}"];
                    $cache[] = 2;
				}

                if($buttons === [])
                {
                    $this->sendModal('§lMarket§r > §8アイテムポスト', 'アイテムがありません', '戻る', '閉じる', 11, 0);
                    return;
                }

				$data = [
					'type'    => 'form',
					'title'   => '§lMarket§r > §8アイテムポスト',
					'content' => "交換したアイテムを受け取ることができます。\n受け取りたいアイテムを選択してください\nアイテムは1スタックずつ受け取ることができます\n\n{$this->message}",
					'buttons' => $buttons
				];

                $this->message = "";
				break;

            case 2:
                $item = $this->items[$this->lastData];

                if($item->getCount() > $item->getMaxStackSize()) $item->setCount($item->getMaxStackSize());

                if($this->player->getInventory()->canAddItem($item))
                {
                    $this->player->getInventory()->addItem($item);
                    PostProvider::get()->removeItem($this->player->getName(), $item);
                    $this->message = $item->getName() . 'を' . $item->getCount() . '個受け取りました';
                }
                else{
                    $this->message = 'インベントリがいっぱいです！';
                }
                break;

            case 11:
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
