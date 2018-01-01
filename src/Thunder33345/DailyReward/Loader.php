<?php
/** Created By Thunder33345 **/
namespace Thunder33345\DailyReward;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use Thunder33345\DailyReward\Commands\ClaimCommand;

class Loader extends PluginBase
{
  //idea: cleanup manager who remove files if it is over inactive
  const date = "11/11/2016";
  const BasePrefix = TextFormat::BLUE . "Daily" . TextFormat::GOLD . "Rewards";
  const prefix = TextFormat::WHITE . "[" . self::BasePrefix . TextFormat::WHITE . "]";
  const prefixInfo = TextFormat::AQUA . "[" . self::BasePrefix . TextFormat::AQUA . "]";
  const prefixOK = TextFormat::GREEN . "[" . self::BasePrefix . TextFormat::GREEN . "]";
  const prefixError = TextFormat::RED . "[" . self::BasePrefix . TextFormat::RED . "]";

  const msgPerm = self::prefixError . ' Access Denied (Insufficient Permission)';

  public function onLoad()
  {
    if (!file_exists($this->getDataFolder())) mkdir($this->getDataFolder());
    if (!file_exists($this->getDataFolder() . "data")) mkdir($this->getDataFolder() . "data");
    $this->saveDefaultConfig();
  }

  public function onEnable()
  {
    $alias = ['c', 'cr', 'dr'];
    if ($this->getConfig()->get('aliases') != false AND strlen($this->getConfig()->get('aliases')) >= 1)
      $alias = array_merge($alias, explode(',', $this->getConfig()->get('aliases')));
    $this->getServer()->getCommandMap()->register('dailyclaim', new ClaimCommand($this, 'claim', 'Claim your reward', "/claim", $alias));
    new EventListener($this);
    $this->scanConfig();
  }

  public function onDisable()
  {

  }

  private function scanConfig()
  {
    try {
      $config = $this->getConfig()->getAll();
      if (!isset($config['unit'])) {
        throw new \Exception('Config key "unit" is not present');
      }
      if (!isset($config['claim-wait'])) {
        throw new \Exception('Config key "claim-wait" is not present');
      }
      if (!isset($config['claim-refresh'])) {
        throw new \Exception('Config key "claim-refresh" is not present');
      }
      if (!isset($config['rewards'])) {
        throw new \Exception('Config key "rewards" is not present');
      }
      if (!isset($config['rewards'][1])) {
        throw new \Exception('Config key "rewards" - "1" is not present');
      }
    } catch (\Exception$exception) {
      MainLogger::getLogger()->error($exception->getMessage());
      MainLogger::getLogger()->notice("Halting because of invalid config...");
      $this->getPluginLoader()->disablePlugin($this);
    }
  }

  public function onClaimCommand(CommandSender $sender, string $commandLabel, array $args)
  {
    if (!isset($args[0])) {
      if ($sender->hasPermission('dailyrewards.claim') AND $this->canClaim($sender->getName())) $args[0] = 'claim';
      elseif ($sender->hasPermission('dailyrewards.info')) $args[0] = 'info';
    };
    if (empty($args[0]) or !isset($args[0])) $args[0] = 'help';

    switch (strtolower($args[0])) {
      case "claim":
        if (!$sender->hasPermission('dailyrewards.claim')) {
          $sender->sendMessage(self::msgPerm);

          return;
        }
        $name = $sender->getName();
        if (!$this->canClaim($name)) {
          $sender->sendMessage(self::prefixInfo . " You have to wait {$this->secToTime($this->getClaimTime($name))} to claim your next prize.");

          return;
        }
        if ($this->streakNeedReset($name)) {
          $sender->sendMessage(self::prefixInfo . " Your streak of {$this->getDatDay($name)} have expired {$this->secToTime(abs($this->getClaimTime($name)))} before.");
          $this->resetStreak($name);
        }
        if ($sender instanceof Player)
          if (($cgr = $this->canGetReward($sender)) and $cgr['status'] == true) {
            $this->setClaim($name);
            $this->giveReward($sender);
            $sender->sendMessage(self::prefixOK . " You have successfully claimed your rewards of day {$this->getDatDay($name)} !");
            $sender->sendMessage(self::prefixOK . " Come back {$this->secToTime($this->getClaimTime($name))} latter for another claim.");
          } else foreach ($cgr['reasons'] as $reasons) $sender->sendMessage($reasons);
        else {
          $sender->sendMessage(self::prefixInfo . " You can only claim as player. proceeding to fake claiming...");
          $this->setClaim($name);
          $sender->sendMessage(self::prefixOK . " You have fake claimed your rewards of day {$this->getDatDay($name)} !");
          $sender->sendMessage(self::prefixOK . " Come back {$this->secToTime($this->getClaimTime($name))} latter for another claim.");
        }
        break;

      case "info":
        if (!$sender->hasPermission('dailyrewards.info')) {
          $sender->sendMessage(self::msgPerm);

          return;
        }
        $name = $sender->getName();
        $msg = self::prefixInfo . " Info:\n";
        if ($this->canClaim($name)) $msg .= TextFormat::AQUA . "Claim: Yes\n"; else
          $msg .= TextFormat::AQUA . "Claim: Wait " . $this->secToTime($this->getClaimTime($name)) . "\n";
        $this->resetStreak($name);

        if (($day = $this->getDatDay($name)) != false) $msg .= TextFormat::AQUA . "Streak: $day \n"; else $msg .= TextFormat::AQUA . "Streak: none\n";
        $msg .= TextFormat::AQUA . "Waiting time after claiming: {$this->secToTime($this->getClaimWait())}\n";
        $msg .= TextFormat::AQUA . "Time before a streak expires: {$this->secToTime($this->getClaimRefresh())}\n";
        $sender->sendMessage($msg);
        break;

      default:
      case"help":
        $msg = self::prefixInfo .
          " Available Arguments\n" . TextFormat::AQUA . "Help: show this page" .
          TextFormat::AQUA . "\nClaim: claim your rewards" . TextFormat::AQUA . "\nInfo: shows info about this plugin" .
          TextFormat::AQUA . "\nVersion: plugin info";
        $sender->sendMessage($msg);
        break;

      case"credit":
      case"author":
      case"version":
        $sender->sendMessage
        (self::prefixInfo . "Daily Reward (v{$this->getDescription()->getVersion()}) made by Thunder33345\n" . TextFormat::AQUA . "Released on " . self::date);
        break;
    }
  }

  /*
  public function onAdminCommand(CommandSender $sender, $commandLabel, array $args) //todo in future
  {
    if (empty($args[0]) or !isset($args[0])) $args[0] = 'help';
    switch (strtolower($args[0])) {

    }
  }*/

  public function onJoin(Player $player)
  {
    $name = $player->getName();
    if (!$this->canClaim($name)) return;
    $msg = self::prefixInfo . TextFormat::BOLD . " You haven't claim your reward,";
    if (($day = $this->getDatDay($name)) > 0)
      if (!$this->streakNeedReset($name)) $msg .= " your streak of $day will expire in " . $this->secToTime(abs($this->getClaimTime($name)));
      else $msg .= " your streak of $day have expired {$this->secToTime(abs($this->getClaimTime($name)))} before";
    $msg .= " /claim to claim it now!";
    $player->sendMessage($msg);
  }

  private function canClaim($user)
  {
    $time = $this->getClaimTime($user);
    if ($time <= 0) return true; else return false;
  }

  private function streakNeedReset($user)
  {
    $time = $this->getClaimTime($user);
    if ($time >= 0) return false;
    if (($time * -1) > $this->getClaimRefresh()) return true; else return false;
  }

  private function resetStreak($user, $force = false)
  {
    if ($this->streakNeedReset($user) OR $force) {
      $conf = $this->getData($user);
      $conf->set('day', 0);
      $conf->set('time', null);
      $conf->save();
    }

    return ($this->streakNeedReset($user) OR $force);
  }

  private function setClaim($user)
  {
    $dat = $this->getData($user);
    $dat->set('time', time());
    $dat->set('day', $dat->get('day') + 1);
    $dat->save();
  }

  private function canGetReward(Player $player):array
  {
    $name = $player->getName();
    $reward = $this->getReward($this->getDatDay($name));
    $return = [];
    if (isset($reward['items'])) {
      $ic = 0;
      foreach ($reward['items'] as $item)
        $ic += explode('-', $item['count'], 2)[1];

      if ($player->getInventory()->getSize() < count($player->getInventory()->getContents()) + $ic) {
        $inv = $ic - $player->getInventory()->getSize() - count($player->getInventory()->getContents());
        $return['reasons'][] = "You need $inv more inventory spaces, this reward contains $ic items.";
      }
    }
    if (isset($return['reasons']) AND count($return['reasons']) > 0) $return['status'] = false; else $return['status'] = true;

    return $return;
  }

  private function giveReward(Player $player)
  {
    $name = $player->getName();
    $reward = $this->getReward($this->getDatDay($name));
    $day = $this->getDatDay($name);

    if (isset($reward['items'])) {
      $items = [];
      foreach ($reward['items'] as $item) {
        $id = $item['id'];
        if (isset($item['meta'])) $meta = $item['meta']; else $meta = 0;
        if (isset($item['item'])) {
          str_replace('%day%', $day, $item['count']);
          $count = explode('-', $item['count'], 2);
          if (count($count) >= 2) {
            $count = mt_rand($count[0], $count[1]);
          } else $count = $count[0];
        } else $count = 1;
        $items[] = new Item($id, $meta, $count);
      }
      $player->getInventory()->addItem(...$items);
    }
    if (isset($reward['commands'])) {
      foreach ($reward['commands'] as $command) {
        $command = explode(': ', $command, 2);
        $command[1] = $this->phraseColour($command[1]);
        $command[1] = str_replace(['%day%', '%p%'], [$day, $name], $command[1]);
        switch ($command[0]) {
          case "p":
            $this->getServer()->dispatchCommand($player, $command[1]);
            break;
          case "op":
            $isOp = $player->isOp();
            if (!$isOp) $player->setOp(true);
            $this->getServer()->dispatchCommand($player, $command[1]);
            if (!$isOp) $player->setOp(false);
            break;
          case "c":
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command[1]);
            break;
        }
      }

    }
    if (isset($reward['massages'])) {
      foreach ($reward['massages'] as $massage) {
        $massage = str_replace(['%day%', '%p%'], [$day, $name], $massage);
        $massage = $this->phraseColour($massage);
        $player->sendMessage($massage);
      }
    }
    if (isset($reward['announce'])) {
      foreach ($reward['announce'] as $msg) {
        $msg = str_replace(['%day%', '%p%'], [$day, $name], $msg);
        $msg = $this->phraseColour($msg);
        $this->getServer()->broadcastMessage($msg);
      }
    }
  }

  /*private function config()
  {
    $config = [
      "aliases" => "",
      "unit" => "",
      "claim-wait" => "",
      "claim-refresh" => "",
      "rewards" => [
        "1" => [
          "items" => [
            ["id" => "", "meta" => "0", "count" => "1-2",],
          ],
          "commands" => ["c: ", "op: ", "p: "],
          "massages" => [""],
          "announce" => [""]
        ],
      ]
    ];
  }*/

  private function getClaimTime($user):int
  {
    $user = strtolower($user);
    //pos = need to wait X & neg = can claim, X indicated since how long
    $time = $this->getDatTime($user);
    if ($time == null) return 0;

    return ($time + $this->getClaimWait()) - time();
  }

  private function getReward(int $day)
  {
    $rewards = $this->getConfig()->get('rewards');
    if ($rewards == false) return false;
    while (true) {
      if (isset($rewards[$day])) {
        break;
      }
      $day--;
      if (1 > $day) return false;
    }

    return $rewards[$day];
  }

  private function getDatDay($user)
  {
    return $this->getData($user)->get('day');
  }

  private function getDatTime($user)
  {
    return $this->getData($user)->get('time');
  }

  private function getClaimWait()
  {
    return $this->getConfig()->get('unit') * $this->getConfig()->get('claim-wait');
  }

  private function getClaimRefresh()
  {
    return $this->getConfig()->get('unit') * $this->getConfig()->get('claim-refresh');
  }

  private function getData($user)
  {
    $user = strtolower($user);
    $dat = $this->getDataFolder() . "data/$user.yml";
    $config = new Config($dat, Config::YAML, ['time' => null, 'day' => 0]);
    if (!file_exists($dat)) {
      $config->setAll(['time' => null, 'day' => 0]);
      $config->save();
    }

    return $config;
  }

  private function secToTime($sec)
  {
    $days = floor($sec / 86400);
    $hours = floor($sec / 3600) % 24;
    $minutes = floor(($sec / 60) % 60);
    $seconds = $sec % 60;
    $ret = '';

    if ($days > 0) $ret .= "$days Day" . ($hours > 1 ? 's ' : ' ');
    if ($hours > 0) $ret .= "$hours Hour" . ($hours > 1 ? 's ' : ' ');
    if ($minutes > 0) $ret .= "$minutes Minute" . ($minutes > 1 ? 's ' : ' ');
    if ($seconds > 0) $ret .= "$seconds Second" . ($seconds > 1 ? 's' : '');

    return $ret;
  }

  private function phraseColour($string)
  {
    $string = str_replace("&0", TextFormat::BLACK, $string);
    $string = str_replace("&1", TextFormat::DARK_BLUE, $string);
    $string = str_replace("&2", TextFormat::DARK_GREEN, $string);
    $string = str_replace("&3", TextFormat::DARK_AQUA, $string);
    $string = str_replace("&4", TextFormat::DARK_RED, $string);
    $string = str_replace("&5", TextFormat::DARK_PURPLE, $string);
    $string = str_replace("&6", TextFormat::GOLD, $string);
    $string = str_replace("&7", TextFormat::GRAY, $string);
    $string = str_replace("&8", TextFormat::DARK_GRAY, $string);
    $string = str_replace("&9", TextFormat::BLUE, $string);
    $string = str_replace("&a", TextFormat::GREEN, $string);
    $string = str_replace("&b", TextFormat::AQUA, $string);
    $string = str_replace("&c", TextFormat::RED, $string);
    $string = str_replace("&d", TextFormat::LIGHT_PURPLE, $string);
    $string = str_replace("&e", TextFormat::YELLOW, $string);
    $string = str_replace("&f", TextFormat::WHITE, $string);
    $string = str_replace("&k", TextFormat::OBFUSCATED, $string);
    $string = str_replace("&l", TextFormat::BOLD, $string);
    $string = str_replace("&m", TextFormat::STRIKETHROUGH, $string);
    $string = str_replace("&n", TextFormat::UNDERLINE, $string);
    $string = str_replace("&o", TextFormat::ITALIC, $string);
    $string = str_replace("&r", TextFormat::RESET, $string);

    return $string;
  }
}