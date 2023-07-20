<?php

namespace UnknowL\handlers;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use pocketmine\utils\Limits;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class ArmorEffectHandler extends Handler
{
	private Config $config;

	protected array $data = [];

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder(). "armor_effects.yml", Config::YAML);
		parent::__construct();
	}

	protected function loadData(): void
    {
		$this->data = $this->config->getAll();
	}

	public function applyEffect(Item $source, LinesiaPlayer $player): void
	{

		$alias = StringToItemParser::getInstance()->lookupAliases($source)[0];
		if(isset($this->data[$alias]))
		{
            foreach($this->data[$alias] as $effect)
			{
				$data = explode(":", $effect);
				$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId($data[0]), Limits::INT32_MAX, $data[1] ?? 0, false));
            }
        }
	}

	public function removeEffect(Item $target, LinesiaPlayer $player): void
	{
		$alias = StringToItemParser::getInstance()->lookupAliases($target)[0];
        if(isset($this->data[$alias]))
        {
            foreach($this->data[$alias] as $effect)
            {
                $data = explode(":", $effect);
                $player->getEffects()->remove(EffectIdMap::getInstance()->fromId($data[0]));
            }
        }
	}

    protected function saveData(): void
    {
		//None
	}

    public function getName(): string
    {
		return "ArmorEffects";
	}
}