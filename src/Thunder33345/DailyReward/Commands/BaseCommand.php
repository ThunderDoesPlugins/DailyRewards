<?php
/*
Copyright (c) 2018 Thunder33345

Permission to use, copy, modify, and distribute this software for any
purpose without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
*/
/** Created By Thunder33345 **/
namespace Thunder33345\DailyReward\Commands;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

abstract class BaseCommand extends Command implements PluginIdentifiableCommand {

	/** @var Plugin */
	protected $plugin;

	/**
	 * Constructor
	 *
	 * @param Plugin $plugin
	 * @param string $name
	 * @param null|string $description
	 * @param array|\string[] $usageMessage
	 * @param array $aliases
	 */
	public function __construct(Plugin $plugin, $name, $description, $usageMessage, $aliases) {
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin():Plugin {
		return $this->plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args):bool {
		return $this->onCommand($sender,$commandLabel, $args);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 *
	 * @return bool
	 */
	public abstract function onCommand(CommandSender $sender,string $commandLabel, array $args):bool;

}