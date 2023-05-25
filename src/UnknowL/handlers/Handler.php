<?php

namespace UnknowL\handlers;

abstract class Handler
{
	protected abstract function loadData(): void;

	protected abstract function saveData(): void;
}