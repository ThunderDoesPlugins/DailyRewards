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