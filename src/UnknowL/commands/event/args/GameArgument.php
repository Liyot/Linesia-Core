<?php

namespace UnknowL\commands\event\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use UnknowL\games\BaseGame;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\BaseArgument;
use UnknowL\lib\commando\args\RawStringArgument;
use UnknowL\lib\commando\args\StringEnumArgument;

final class GameArgument extends StringEnumArgument
{

	public static array $VALUES = [];

	public function __construct(string $name, bool $optional = false)
	{
		parent::__construct($name, $optional);
	}

	public function getValue(string $string) {
		return self::$VALUES[strtolower($string)];
	}

	public function getEnumValues(): array {
		return array_keys(self::$VALUES);
	}

	final public function getEnumName(): string
	{
		return "game";
	}

	/**
	 * @inheritDoc
	 */
	public function parse(string $argument, CommandSender $sender): BaseGame
	{
		return Handler::GAME()->getGame($this->getValue($argument));
	}

	public function getTypeName(): string
	{
		return 'game';
	}
}