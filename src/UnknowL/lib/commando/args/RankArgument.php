<?php

namespace UnknowL\lib\commando\args;

use pocketmine\command\CommandSender;
use UnknowL\handlers\Handler;

class RankArgument extends StringEnumArgument
{

	public static array $VALUES = [];


	public function getValue(string $string) {
		return self::$VALUES[strtolower($string)];
	}

	public function getEnumValues(): array {
		return array_keys(self::$VALUES);
	}

	public function getEnumName(): string
	{
		return "rank";
	}


    /**
     * @inheritDoc
     */
    public function parse(string $argument, CommandSender $sender): mixed
    {
		return Handler::RANK()->getRank($this->getValue($argument));
	}


    public function getTypeName(): string
    {
		return 'rank';
	}
}