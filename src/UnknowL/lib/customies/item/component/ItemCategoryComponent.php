<?php

namespace UnknowL\lib\customies\item\component;

class ItemCategoryComponent implements ItemComponent
{

    public function getName(): string
    {
		return "";
	}

    public function getValue(): mixed
    {
		return '';
	}

    public function isProperty(): bool
    {
		return true;
	}
}