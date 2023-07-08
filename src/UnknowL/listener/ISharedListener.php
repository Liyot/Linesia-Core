<?php

namespace UnknowL\listener;

use pocketmine\event\Event;

interface ISharedListener
{
	public function onEvent(Event $event): void;

	public function getEventName(): string;

}