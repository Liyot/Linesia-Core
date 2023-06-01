<?php

namespace UnknowL\utils;

use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\PlayerProperties;

final class Cooldown
{

	private array $args = [];

	private bool $finish = false;

	public const TYPE_PLAYER = 0, TYPE_EVENT = 1;

	private string $lastParsedDate;

	public function __construct(private \Closure $closure, private int $days = 0, private int $hours = 0, private int $minutes = 0, private int $seconds = 0, private string $path = "", ...$args)
	{
		$this->args = $args;
		$this->lastParsedDate = \date('d:H:i:s');
		Linesia::getInstance()->getCooldownHandler()->add($this);
	}

	final public function format(): string
	{
		return sprintf("Il reste %d jours %d heures %d minutes et %d secondes", $this->days, $this->hours, $this->minutes, $this->seconds);
	}

	final public function end(): bool
	{
		if(($this->days + $this->hours + $this->minutes + $this->seconds) === 0)
		{
			$this->closure->__invoke($this->args);
			$array = array_filter($this->args, fn($value) => $value instanceof LinesiaPlayer);
			count($array) === 0 ?: $this->saveProperties($array[0]->getPlayerProperties());
			$this->finish = true;
			return true;
		}
		return false;
	}

	final public function actualize(): void
	{
		[$value1 , $value2, $value3, $value4] = explode(":", $this->lastParsedDate);
		[$data1, $data2, $data3, $data4] = explode(":", date("d:H:i:s"));

		$this->days -= $data1 - $value1;
		$this->hours -= $data2 - $value2;
		$this->minutes -= $data3 - $value3;
		$this->seconds -= $data4 - $value4;

		$this->lastParsedDate = date("d:H:i:s");

		$this->end();
	}

	protected function serialize(): ?string
	{
		return (($this->days + $this->hours + $this->minutes + $this->seconds) !== 0) ? sprintf("%d:%d:%d:%d:%s",$this->days, $this->hours, $this->minutes, $this->seconds, $this->path) : null;
	}

	public function saveProperties(PlayerProperties $properties): mixed
	{
		if(!empty($this->path))
		{
			$properties->setNestedProperties($this->path, $this->serialize());
			return $properties;
		}
		return true;
	}

	public static function withoutValue(): self
	{
		return new self(\Closure::fromCallable(function () {}));
	}

	/**
	 * @return int
	 */
	final public function getHours(): int
	{
		return $this->hours;
	}

	/**
	 * @return int
	 */
	final public function getMinutes(): int
	{
		return $this->minutes;
	}

	/**
	 * @return int
	 */
	final public function getSeconds(): int
	{
		return $this->seconds;
	}

	/**
	 * @return int
	 */
	final public function getDays(): int
	{
		return $this->days;
	}

	/**
	 * @return bool
	 */
	final public function isFinish(): bool
	{
		return $this->finish;
	}
}