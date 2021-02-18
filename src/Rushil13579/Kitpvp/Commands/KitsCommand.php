<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;
use pocketmine\command\{Command, CommandSender};
use Rushil13579\Kitpvp\Kitpvp;

class KitsCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('kits', 'Lists all available kits');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      $kits = '';
      foreach(array_keys($this->plugin->kits->get('kits')) as $kit){
        $kits .= $kit . ", ";
      }
      $s->sendMessage("§3Kits: §b$kits");
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
