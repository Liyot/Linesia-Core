<?php

namespace UnknowL\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;

class SimpleSharedListener
{
	public function __construct(private PlayerListener $listener, private ISharedListener $sharedListener)
	{}

	public function onSharedEvent(Event $event): void
	{
		if ($event::class === $this->sharedListener->getEventName())
		{
			if (!$this->isMultiHandled())
			{
				$this->sharedListener->onEvent($event);
			}
		}
	}

	public function isMultiHandled(): bool
	{
		return count(array_filter($this->listener->sharedListeners, fn(self $class) => $class->sharedListener === $this->sharedListener)) > 1;
	}
}