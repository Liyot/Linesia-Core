<?php

namespace UnknowL\handlers\dataTypes;

final class Tag
{

	private bool $isDisabled = false;
	public function __construct(private string $name, private string $format, private int $price, private string $permission = "pocketmine.group.user") {}

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
	public function getFormat(): string
	{
		$format = preg_replace('/[{}]/', '', $this->format);
		str_replace('TagName', $format, $this->format);
		str_contains($format, 'TagName') ? $format = "" : true;
		return $this->isDisabled ? "§cAucun" : $format;
	}

	/**
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->price;
	}

	final public function disable(bool $value = true): void
	{
		$this->isDisabled = $value;
	}

	/**
	 * @return string
	 */
	public function getPermission(): string
	{
		return $this->permission;
	}
}