<?php

namespace market\provider\providers;

use pocketmine\item\Item;
use pocketmine\Player;

class TradeProvider extends Provider
{

	const PROVIDER_ID = "trade_provider";

	public function open()
	{
        if(!file_exists($this->plugin->getDataFolder() . "trade")) mkdir($this->plugin->getDataFolder() . "trade", 0744, true);
        if(!file_exists($this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . "index")) mkdir($this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . "index", 0744, true);
    }

	public function close()
    {

    }

    public function make(string $owner, Item $exhibit, Item $request)
    {
        $id = uniqid();
        $data = [
            "id" => $id,
            "exhibitor" => $owner,
            "exhibit" => [
                "id" => $exhibit->getId(),
                "damage" => $exhibit->getDamage(),
                "amount" => $exhibit->getCount(),
                "name" => $exhibit->getName()
            ],
            "request" => [
                "id" => $request->getId(),
                "damage" => $request->getDamage(),
                "amount" => $request->getCount(),
                "name" => $request->getName()
            ]
        ];
        yaml_emit_file($this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . $id . ".yml", $data, YAML_UTF8_ENCODING);

        $indexPath = $this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . "index" . DIRECTORY_SEPARATOR . $owner . ".yml";
        $indexData = file_exists($indexPath) ? yaml_parse_file($indexPath) : [];
        $indexData[] = $id;
        yaml_emit_file($indexPath, $indexData, YAML_UTF8_ENCODING);
    }

    public function delete($id)
    {
        $path = $this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . $id . ".yml";
        if(file_exists($path))
        {
            $data = yaml_parse_file($path);
            $indexPath = $this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . "index" . DIRECTORY_SEPARATOR . $data["exhibitor"] . ".yml";
            if(file_exists($indexPath))
            {
                $indexData =  yaml_parse_file($indexPath);
                unset($indexData[array_search($id, $indexData)]);
                yaml_emit_file($indexPath, $indexData, YAML_UTF8_ENCODING);
            }
            unlink($path);
        }
    }

    public function getTrades(int $to, int $from = 0)
    {
        $data = [];
        $count = 0;
        foreach(glob($this->plugin->getDataFolder() . 'trade' . DIRECTORY_SEPARATOR . '*') as $file){
            if(is_file($file) && $count >= $from){
                $data[] = yaml_parse_file($file);
            }
            $count++;
            if($count > $to) break;
        }

        return $data;
    }

    public function getTrade($id)
    {
        $path = $this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . $id . ".yml";
        if(file_exists($path))
        {
            return yaml_parse_file($path);
        }

        return null;
    }

    public function getIndex(string $name)
    {
        $indexPath = $this->plugin->getDataFolder() . "trade" . DIRECTORY_SEPARATOR . "index" . DIRECTORY_SEPARATOR . $name . ".yml";

        if(file_exists($indexPath)) return yaml_parse_file($indexPath);

        return [];
    }

}