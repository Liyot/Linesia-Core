<?php

declare(strict_types=1);

namespace UnknowL\lib\forms;

use JetBrains\PhpStorm\Immutable;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use UnknowL\player\LinesiaPlayer;
use function gettype;
use function is_bool;

#[Immutable]
class ModalForm extends BaseForm{

	/** @phpstan-param \Closure(LinesiaPlayer, bool) : mixed $onSubmit */
	public function __construct(
		string $title,
		public /*readonly*/ string $content,
		private /*readonly*/ \Closure $onSubmit,
		public /*readonly*/ string $button1 = "gui.yes",
		public /*readonly*/ string $button2 = "gui.no",
	){
		/** @phpstan-ignore-next-line */
		Utils::validateCallableSignature(function(LinesiaPlayer $player, bool $choice){ }, $onSubmit);
		parent::__construct($title);
	}

	/** @phpstan-param \Closure(LinesiaPlayer) : mixed $onConfirm */
	public static function confirm(string $title, string $content, \Closure $onConfirm) : self{
		/** @phpstan-ignore-next-line */
		Utils::validateCallableSignature(function(LinesiaPlayer $player){ }, $onConfirm);
		return new self($title, $content, static function(LinesiaPlayer $player, bool $response) use ($onConfirm) : void{
			if($response){
				$onConfirm($player);
			}
		});
	}

	protected function getType() : string{ return "modal"; }

	protected function serializeFormData() : array{
		return [
			"content" => $this->content,
			"button1" => $this->button1,
			"button2" => $this->button2,
		];
	}

	/**
	 * @param LinesiaPlayer $player
	 * @param mixed $data
	 * @return void
	 */
	final public function handleResponse(Player $player, mixed $data) : void{
		if(!is_bool($data)){
			throw new FormValidationException("Expected bool, got " . gettype($data));
		}

		($this->onSubmit)($player, $data);
	}
}
