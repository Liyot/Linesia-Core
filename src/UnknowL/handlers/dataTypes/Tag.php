<?php

namespace UnknowL\handlers\dataTypes;

final class Tag
{

	private bool $isDisabled = false;
	public function __construct(private string $name, private string $format, private int $price) {}

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
		return $this->isDisabled ? "" : $this->format;
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
}