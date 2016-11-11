<?php

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
	public function getPlugin() {
		return $this->plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args) {
		return $this->onCommand($sender,$commandLabel, $args);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 *
	 * @return bool
	 */
	public abstract function onCommand(CommandSender $sender,$commandLabel, array $args);

}