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
			"dual" => [],
		])]
	private array $requests = [];

    /**
     * @var $requests list<array<list<Request>>>
     */
    #[ArrayShape(
        [
            "dual" => [],
        ])]
    private array $queue = [];

    protected function loadData(): void
	{ /*None*/ }

    protected function saveData(): void
    {/*None*/}

	final public function addRequest(Request $request)
	{
		$this->requests[$request->$request()][$request->getId()] = $request;
        i
	}

    final public function sendRequestToQueue(Request $request): void
    {
        if ($request->getName() === "dual" && empty($this->queue["dual"])) $request->accept();
            $this->queue[$request->getName()][] = $request;
    }

    protected function startNextDual(): void
    {
        if (empty($this->queue)) return;
        

    }

	final public function removeRequest(Request $request): void
	{
        if (isset($this->queue[$request->getName()]))
		unset($this->requests[$request->getName()][$request->getId()]);
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