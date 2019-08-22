<?php

namespace market\form\forms;

use pocketmine\Player;

use market\form\forms\Form;

class MenuForm extends Form
{

	public function send(int $id)
	{

		$cache = [];
		$data = [];

		switch($id)
		{
			case 1:
				$buttons = [];
				$buttons[] = ['text' => '取引'];
				$buttons[] = ['text' => '出品'];
                $buttons[] = ['text' => 'アイテムポスト'];
                $buttons[] = ['text' => '出品管理'];
                $cache[] = 11;
                $cache[] = 12;
                $cache[] = 13;
                $cache[] = 14;
				$data = [
					'type'    => 'form',
					'title'   => '§lMarket',
					'content' => "マーケットへようこそ！！\nここではアイテムを物々交換できます！！\n\n",
					'buttons' => $buttons
				];
				break;

            case 11:
                $this->close();
                MarketForm::create($this->player);
                break;

            case 12:
                $this->close();
                ExhibitForm::create($this->player);
                break;

            case 13:
                $this->close();
                PostForm::create($this->player);
                break;

            case 14:
                $this->close();
                MarketEditForm::create($this->player);
                break;

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
