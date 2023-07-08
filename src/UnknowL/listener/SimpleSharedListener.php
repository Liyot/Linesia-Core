<?php

namespace UnknowL\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;

class SimpleSharedListener
{
	public function __construct(private Listener $listener, private ISharedListener $sharedListener)
	{}

	public function onSharedEvent(Event $event): void
	{
	}
}