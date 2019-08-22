<?php

namespace market\provider\providers;

use pocketmine\item\Item;

class PostProvider extends Provider
{

	const PROVIDER_ID = "account_provider";

	public function open()
	{
        if(!file_exists($this->plugin->getDataFolder() . "post")) mkdir($this->plugin->getDataFolder() . "post", 0744, true);
    }

	public function close()
    {

    }

    public function addItem(string $name, Item $item)
    {
        $this->check($name);

        $data = yaml_parse_file($this->getPath($name));
        $key = "{$item->getId()}:{$item->getDamage()}";
        if(isset($data[$key])) $data[$key] += $item->getCount();
        else $data[$key] = $item->getCount();

        yaml_emit_file($this->getPath($name), $data, YAML_UTF8_ENCODING);
    }

    public function removeItem(string $name, Item $item)
    {
        $this->check($name);

        $data = yaml_parse_file($this->getPath($name));
        $key = "{$item->getId()}:{$item->getDamage()}";

        if(isset($data[$key]))
        {
            $data[$key] -= $item->getCount();
            if($data[$key] <= 0) unset($data[$key]);
        }

        yaml_emit_file($this->getPath($name), $data, YAML_UTF8_ENCODING);
    }

    public function getItemData(string $name)
    {
        $this->check($name);

        return yaml_parse_file($this->getPath($name));
    }

    public function check(string $name)
    {
        if(!$this->exist($name))
        {
            yaml_emit_file($this->getPath($name), [], YAML_UTF8_ENCODING);
        }
    }

    public function exist(string $name)
    {
        return file_exists($this->getPath($name));
    }

    public function getPath(string $name)
    {
        return $this->plugin->getDataFolder() . "post" . DIRECTORY_SEPARATOR . $name . ".yml";
    }

}