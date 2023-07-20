<?php

namespace UnknowL\handlers;

class VoteHandler extends Handler
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

	}

    public function getName(): string
    {
		return 'Vote';
	}
}