<?php

namespace UnknowL\handlers;

use pocketmine\utils\Config;
use UnknowL\handlers\dataTypes\Tag;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class TagHandler extends Handler
{


	public function __construct()
	{
		parent::__construct();
	}

	/**@var Tag[]*/
	private array $tags = [];


	protected function loadData(): void
	{
		$config = new Config(Linesia::getInstance()->getDataFolder()."tags.yml", Config::YAML);
		foreach ($config->getAll() as $name => $tagData)
		{
			$this->tags[$name] = new Tag($name, $tagData["format"], $tagData["price"]);
		}
	}

	final public function setTag(LinesiaPlayer $player, Tag $tag): void
	{
		if ($player->hasTag($tag->getName()))
		{
			$player->setTag($tag);
		}
	}

	final public function buyTag(LinesiaPlayer $player, Tag $tag): void
	{
		if (!$player->hasTag($tag->getName()))
		{
			if ($player->getEconomyManager()->reduce($tag->getPrice()))
			{
				$player->setTag($tag);
				$player->addPermission("perm.tag.{$tag->getName()}");
			}
		}
	}

	final public function getTag(string $name): ?Tag
	{
		return $this->tags[$name] ?? null;
	}

	final public function getTags(): array
	{
		return $this->tags;
	}

	protected function saveData(): void
	{
		//NOOP
	}

	public function getName(): string
	{
		return "Tag";
	}
}