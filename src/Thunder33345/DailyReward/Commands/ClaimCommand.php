<?php
/** Created By Thunder33345 **/
namespace Thunder33345\DailyReward\Commands;

use pocketmine\command\CommandSender;
use Thunder33345\DailyReward\Commands\BaseCommand;
use Thunder33345\DailyReward\Loader;

class ClaimCommand extends BaseCommand
{
  private $loader;

  public function __construct(Loader $plugin, $name, $description, $usageMessage, array $aliases)
  {
    parent::__construct($plugin, $name, $description, $usageMessage, $aliases);
    $this->loader = $plugin;
  }

  public function onCommand(CommandSender $sender, $commandLabel, array $args)
  {
    $this->loader->onClaimCommand($sender,$commandLabel,$args);
  }
}