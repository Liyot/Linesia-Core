<?php

namespace UnknowL\handlers\dataTypes\requests;

use pocketmine\network\mcpe\protocol\StartGamePacket;
use UnknowL\handlers\Handler;
use UnknowL\player\LinesiaPlayer;

//WARN: Il faut que tu enlève la request de l'handler dans ta child class
abstract class Request
{

	private bool $request = false;

	public function __construct(private LinesiaPlayer $from, private LinesiaPlayer $to) {}

	abstract public function getName(): string;

	abstract public function getChatFormat(): string;

	public function send(): void
	{
		$this->from->sendMessage(sprintf("[%s] Vous avez envoyé une demande de %s à %s", $this->getChatFormat(), ucfirst($this->getName()), $this->to->getDisplayName()));
		$this->to->sendMessage(sprintf("[%s] %s vous à envoyé une demande de %s", $this->getChatFormat(), $this->getFrom()->getDisplayName(), ucfirst($this->getName())));
	}

	 public function accept(): void
	{
		if ($this->from->getActiveInteraction(LinesiaPlayer::INTERACTION_REQUEST) && $this->to->getActiveInteraction(LinesiaPlayer::INTERACTION_REQUEST))
		{
			$this->setResult(true);
			$this->process();
			return;
		}
		$this->from->sendMessage($this->getErrorMessage());
		$this->to->sendMessage($this->getErrorMessage());
	}

	 public function decline(): void
	{
		$this->setResult(false);
		$this->process();
	}

	protected function process(): void
	{
		$this->from->sendMessage(sprintf("[%s] Votre demande de %s à été %s", $this->getChatFormat(), $this->getName(), $this->getResult() ? "acceptée" : "refusée"));
		$this->to->sendMessage(sprintf("[%s] Vous avez refusé la demande de %s de %s", $this->getChatFormat(), $this->getName(), $this->from->getDisplayName()));
	}

	final protected function getErrorMessage(): string
	{
		return sprintf("[%s] Echec de la demande de %s", $this->getChatFormat(), ucfirst($this->getName()));
	}

	final public function setResult(bool $result)
	{
		$this->request = $result;
	}

	protected function getResult(): bool
	{
		return $this->request;
	}

	/**
	 * @return LinesiaPlayer
	 */
	final public function getFrom(): LinesiaPlayer
	{
		return $this->from;
	}

	/**
	 * @return LinesiaPlayer
	 */
	final public function getTo(): LinesiaPlayer
	{
		return $this->to;
	}
}