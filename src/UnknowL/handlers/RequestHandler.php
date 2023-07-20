<?php

namespace UnknowL\handlers;

use JetBrains\PhpStorm\ArrayShape;
use UnknowL\handlers\dataTypes\requests\DualRequest;
use UnknowL\handlers\dataTypes\requests\Request;
use UnknowL\player\LinesiaPlayer;

class RequestHandler extends Handler
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @var $requests list<array<list<Request>>>
	 */
	#[ArrayShape(
		[
			"teleportation" => [],
			"dual" => []
		])]
	private array $requests = [];

    protected function loadData(): void
	{ /*None*/ }

    protected function saveData(): void
    {/*None*/}

	final public function addRequest(Request $request)
	{
		$this->requests[$request->getName()][] = $request;
	}

	final public function removeRequest(Request $request): void
	{
		unset($this->requests[$request->getName()][array_search($request, $this->requests[$request->getName()], true)]);
	}

	final public function getActiveDual(LinesiaPlayer $player): DualRequest
	{
		return array_values(array_filter($this->requests['dual'],
			fn(DualRequest $value) =>
				$value->getTo()->getUniqueId()->toString() === $player->getUniqueId()->toString()
				||
				$value->getFrom()->getUniqueId()->toString() === $player->getUniqueId()->toString()

		))[0];
	}

    public function getName(): string
    {
		return "Request";
	}
}