<?php

namespace UnknowL\commands;

use pocketmine\Server;
use pocketmine\utils\Config;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class CommandManager
{
	private Config $config;

	public function __construct()
	{
		$this->config = Linesia::getInstance()->getConfig();
	}

	public function getSettings(string $name): CommandSettings
	{
		return new CommandSettings($this->config->get("commands")[$name]);
	}

}