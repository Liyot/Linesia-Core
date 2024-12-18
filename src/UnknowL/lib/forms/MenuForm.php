<?php

declare(strict_types=1);

namespace UnknowL\lib\forms;

use UnknowL\lib\forms\menu\Button;
use JetBrains\PhpStorm\Immutable;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use UnknowL\player\LinesiaPlayer;
use function gettype;
use function is_int;
use function is_null;

class MenuForm extends BaseForm{

	/**
	 * @phpstan-param list<Button> $buttons
	 * @phpstan-param (\Closure(LinesiaPlayer, Button) : mixed)|null $onSubmit
	 * @phpstan-param (\Closure(LinesiaPlayer) : mixed)|null $onClose
	 */
	public function __construct(
		string $title,
		#[Immutable] public /*readonly*/ string $content = "",
		public array $buttons = [],
		#[Immutable] private /*readonly*/ ?\Closure $onSubmit = null,
		#[Immutable] private /*readonly*/ ?\Closure $onClose = null,
	){
		if($onSubmit !== null){
			/** @phpstan-ignore-next-line */
			Utils::validateCallableSignature(function(LinesiaPlayer $player, Button $selected){ }, $onSubmit);
		}
		if($onClose !== null){
			/** @phpstan-ignore-next-line */
			Utils::validateCallableSignature(function(LinesiaPlayer $player){ }, $onClose);
		}
		parent::__construct($title);
	}

	/** @phpstan-param list<string> $options */
	public static function withOptions(
		string $title,
		string $content = "",
		array $options = [],
		?\Closure $onSubmit = null,
		?\Closure $onClose = null,
	) : self{
		/** @var Button[] $buttons */
		$buttons = [];
		foreach($options as $option){
			$buttons[] = new Button($option);
		}
		return new self($title, $content, $buttons, $onSubmit, $onClose);
	}

	public function appendOptions(string ...$options) : void{
		foreach($options as $option){
			$this->buttons[] = new Button($option);
		}
	}

	public function appendButtons(Button ...$buttons) : void{
		foreach($buttons as $button){
			$this->buttons[] = $button;
		}
	}

	protected function getType() : string{ return "form"; }

	protected function serializeFormData() : array{
		return [
			"buttons" => $this->buttons,
			"content" => $this->content,
		];
	}

	private function getButton(int $index) : Button{
		return $this->buttons[$index] ?? throw new FormValidationException("Button with index $index does not exist");
	}

	final public function handleResponse(Player $player, mixed $data) : void{
		match (true) {
			is_null($data) => $this->onClose?->__invoke($player),
			is_int($data) => $this->onSubmit?->__invoke($player, $this->getButton($data)->setValue($data)),
			default => throw new FormValidationException("Expected int or null, got " . gettype($data)),
		};
	}
}
