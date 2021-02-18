<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;

use pocketmine\command\{Command, CommandSender};

use Rushil13579\Kitpvp\Kitpvp;

class WarpsCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('warps', 'Lists all available warps');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      $list = array_diff(scandir($this->plugin->getDataFolder() . 'Warps/'), array('.', '..'));
      $list = implode(', ', $list);
      $s->sendMessage("§bWarps: §6[$list]");
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
