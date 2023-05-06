<?php

namespace UnknowL\utils;

final class Cooldown
{
	public function __construct(private int $days = 0, private int $hours = 0, private int $minutes = 0, private int $seconds = 0)
	{

	}

	final public function format(): string
	{
		return sprintf("Il reste %d jours %d heures %d minutes et %d secondes", $this->days, $this->hours, $this->minutes, $this->seconds);
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
}