<?php
declare(strict_types=1);

namespace UnknowL\lib\customies;

use UnknowL\lib\customies\block\CustomiesBlockFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

final class Customies extends PluginBase {

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents(new CustomiesListener(), $this);


	}
}
