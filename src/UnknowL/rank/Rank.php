<?php

namespace UnknowL\rank;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use UnknowL\player\LinesiaPlayer;

final class Rank
{
	public function __construct(protected string $name, private string $chatFormat, private array $permissions, private bool $default)
	{
		array_map(fn(string $permission) => PermissionManager::getInstance()->addPermission(new Permission($permission)),$permissions);
	}

	final public function addPermission(string $perm): void
	{
		$this->permissions[] = $perm;
	}

	final public function handleMessage(string $message, LinesiaPlayer $player): string
	{
		$args = [];
		foreach (explode("{", $message) as $argument)
		{
			$name = substr($argument, 0, stripos($argument, "}"));
			!str_contains('&', $name) ?: $name = substr($name, stripos('&', 1));
			$args[] = match (true) {
				str_contains($name, 'faction') => 'dont exist',
				str_contains($name,'prefix') => $this->chatFormat,
				str_contains($name,'role') => ucfirst($this->getName()),
				str_contains($name,'playerName') => $player->getName(),
				str_contains($name, 'message') => $message
			};
		}
		foreach ($args as $name => $value)
		{
			str_replace(sprintf('{%s}', $name), $value, $message);
		}
		return $message;
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