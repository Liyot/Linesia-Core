<?php

namespace UnknowL\task;

use pocketmine\event\Event;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\scheduler\Task;
use UnknowL\listener\ISharedListener;

class ChatGameTask extends Task implements ISharedListener
{

	private static string $expectedResponse = "";
    /**
     * @inheritDoc
     */
    public function onRun(): void
    {

	}

	public static function expectedResponse(): string
	{
		return self::$expectedResponse;
	}

	/**@var PlayerChatEvent $event*/
	public function onEvent(Event $event): void
	{

	}

	public function getEventName(): string
	{
		return PlayerChatEvent::class;
	}
}