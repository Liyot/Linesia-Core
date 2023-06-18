<?php

namespace UnknowL\handlers;

class FactionHandler extends Handler
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function loadData(): void
    {

	}

    protected function saveData(): void
    {
        // TODO: Implement saveData() method.
    }

	public function getName(): string
	{
		return "Faction";
	}
}