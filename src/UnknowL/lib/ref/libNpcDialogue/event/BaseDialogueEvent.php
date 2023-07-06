<?php

declare(strict_types=1);

namespace UnknowL\lib\ref\libNpcDialogue\event;

use pocketmine\event\Event;
use UnknowL\lib\ref\libNpcDialogue\NpcDialogue;

abstract class BaseDialogueEvent extends Event{

	public function __construct(protected NpcDialogue $dialogue){ }

	public function getDialogue() : NpcDialogue{
		return $this->dialogue;
	}
}