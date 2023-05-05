<?php

namespace UnknowL\commands;

final class CommandSettings
{

	/**
	 * @var CommandSettings[]
	 */
	private array $subs = [];

	private string $name = "";

	private string $description = "";

	private array $aliases = [];

	public function __construct(array $settings)
	{
		$this->name = $settings["name"];
		$this->description = $settings["description"];
		$this->aliases = $settings["aliases"];
		!isset($settings["sub"]) ?: $this->parseSubCommands($settings["sub"]);
	}

	/**
	 * @return array
	 */
	final public function getSubs(): array
	{
		return $this->subs;
	}

	final public function getSubSettings(string $name): self
	{
		return $this->getSubSettings($name);
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	final protected function parseSubCommands(array $settings): void
	{
		foreach ($settings as $name => $setting)
		{
			$this->subs[$name] = new self($setting);
		}
	}

	/**
	 * @return string
	 */
	final public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	final public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @return array
	 */
	final public function getAliases(): array
	{
		return $this->aliases;
	}

}