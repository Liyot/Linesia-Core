<?php

namespace UnknowL\utils;

abstract class TimeUtils
{
	public static function formatLeftTime(int $seconds): string
	{
		$days = floor($seconds / 86400);
		$seconds -= $days * 86400;
		$hours = floor($seconds / 3600);
		$seconds -= $hours * 3600;
		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;
		$seconds = $seconds < 10? "0{$seconds}" : $seconds;
		return "{$days} jours, {$hours} heures, {$minutes} minutes, {$seconds} secondes";
	}
}