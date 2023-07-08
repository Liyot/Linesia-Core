<?php

namespace UnknowL\rank;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use UnknowL\player\LinesiaPlayer;

final class Rank
{
	public function __construct(protected string $name, private string $chatFormat, private array $permissions, private bool $default)
	{
		var_dump($this->chatFormat);
		array_map(fn(string $permission) => PermissionManager::getInstance()->addPermission(new Permission($permission)),$permissions);
	}

	final public function addPermission(string $perm): void
	{
		$this->permissions[] = $perm;
	}

	final public function handleMessage(string $message, LinesiaPlayer $player): string
	{
		$chatFormat = $this->chatFormat;

		$toReplace = ["faction" => 'dont exist ', 'prefix' => 'prefix', 'rank' => ucfirst($this->getName()), 'playerName' => $player->getName(), 'message' => $message, 'tag' => 'no tag'];

		for ($i = self::countCharOccurences($chatFormat, '{'); $i >= 0 ; $i--)
		{
			$fullName = substr($chatFormat, stripos($chatFormat, '{'), (stripos($chatFormat, '}') + 1) - stripos($chatFormat, '{'));

			$name = preg_replace('/[{}]/', '', $fullName);
			$name = str_replace('&', '',$name);
			foreach ($toReplace as $key => $value)
			{
				if (str_contains($name, $key))
				{
					$chatFormat = str_replace($fullName, str_replace($fullName, $value, $fullName), $chatFormat);
				}
			}
		}
//			$args[] = match (true) {
//				str_contains($name, 'faction') => 'dont exist',
//				str_contains($name,'prefix') => 'prefix',
//				str_contains($name,'rank') => ucfirst($this->getName()),
//				str_contains($name,'playerName') => $player->getName(),
//				str_contains($name, 'message') => $message
//			};
		return $chatFormat;
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


}