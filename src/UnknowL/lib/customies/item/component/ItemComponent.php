<?php
declare(strict_types=1);

namespace UnknowL\lib\customies\item\component;

interface ItemComponent {

	public function getName(): string;

	public function getValue(): mixed;

	public function isProperty(): bool;
}