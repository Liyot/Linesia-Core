<?php

namespace UnknowL\rank;

final class Rank
{
	public function __construct(protected string $name, private string $chatFormat, private array $permissions, private bool $default)
	{
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

	final public function handleMessage(string $message, array $args): string
	{
		foreach ($args as $name => $value)
		{
			str_replace(sprintf('{%s}', $name), $value, $message);
		}
		return $message;
	}


}