<?php

namespace UnknowL\rank;

use DaPigGuy\PiggyFactions\PiggyFactions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use UnknowL\player\LinesiaPlayer;

final class Rank
{
	public function __construct(protected string $name, private string $chatFormat, private array $permissions, private bool $default, private string $nametagFormat, private int $marketTaxes = 0)
	{
		array_map(fn(string $permission) => PermissionManager::getInstance()->addPermission(new Permission($permission)),$permissions);
	}

	final public function addPermission(string $perm): void
	{
		$this->permissions[] = $perm;
	}

	final public function handleMessage(string $message, LinesiaPlayer $player): string
	{
		$chatFormat = $this->chatFormat;

		$faction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($player)->getFaction();

		$toReplace = [
			"faction" => is_null($faction) ? "§cAucune" : $faction->getName(),
			'prefix' => 'prefix',
			'rank' => ucfirst($this->getName()),
			'playerName' => $player->getName(),
			'message' => $message,
			'tag' => $player->getTag()->getFormat()
		];

		return $this->replaceMessageWithArgs($chatFormat, $toReplace);
	}

	final public function replaceMessageWithArgs(string $configString, array $args): string
	{
		for ($i = self::countCharOccurences($configString, '{'); $i >= 0 ; $i--)
		{
			$fullName = substr($configString, stripos($configString, '{'), (stripos($configString, '}') + 1) - stripos($configString, '{'));

			$name = preg_replace('/[{}]/', '', $fullName);
			$name = str_replace('&', '',$name);
			foreach ($args as $key => $value)
			{
				if (str_contains($name, $key))
				{
					$configString = str_replace($fullName, str_replace($fullName, $value, $fullName), $configString);
				}
			}
		}
		return $configString;
	}

	public static function countCharOccurences(string $string, string $char): int
	{
		$count = 0;
		$lenght = strlen($string);

		for ($i = 0; $i < $lenght; $i++) {
			if ($string[$i] === $char) {
				$count++;
			}
		}
		return $count;
	}

	final public function getNametag(LinesiaPlayer $player): string
	{
		$faction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($player)?->getFaction();
		if (is_null($faction))
		{
			return $this->replaceMessageWithArgs($this->nametagFormat, ["faction" => "§cAucune", "playerName" => $player->getName()]);
		}

		return $this->replaceMessageWithArgs($this->getNametagFormat(), ["faction" => $faction->getName(), "playerName" => $player->getName()]);
	}

	final public function testPermission(string $perm): bool
	{
		return in_array(strtolower($perm), $this->permissions, true);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getChatFormat(): string
	{
		return $this->chatFormat;
	}

	/**
	 * @return array
	 */
	public function getPermissions(): array
	{
		return $this->permissions;
	}

	/**
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return $this->default;
	}

	/**
	 * @return int
	 */
	final public function getMarketTaxes(): int
	{
		return $this->marketTaxes;
	}

	/**
	 * @return string
	 */
	public function getNametagFormat(): string
	{
		return $this->nametagFormat;
	}
}