<?php
/** Created By Thunder33345 **/
namespace Thunder33345\DailyReward;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class EventListener implements Listener
{
  private $server, $loader;

  public function __construct(Loader $loader)
  {
    $this->loader = $loader;
    $this->server = $loader->getServer();
    $loader->getServer()->getPluginManager()->registerEvents($this, $loader);
  }

  public function PlayerJoinEV(PlayerJoinEvent $event)
  {
    if($event->getPlayer() instanceof Player)$this->loader->onJoin($event->getPlayer());
  }
}