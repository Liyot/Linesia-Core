<?php

namespace UnknowL\rank;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

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

	final public function handleMessage(string $message, array $args): string
	{
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