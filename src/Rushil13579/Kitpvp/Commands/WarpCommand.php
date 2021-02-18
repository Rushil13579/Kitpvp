<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;

use pocketmine\command\{Command, CommandSender};

use pocketmine\utils\Config;

use Rushil13579\Kitpvp\Kitpvp;

class WarpCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('warp', 'Warp command');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      if(isset($args[0])){
        $warpname = $args[0];
        if($warpname == '.' or $warpname == '..'){
          $s->sendMessage('§cError, please try again');
          return true;
        }
        if(file_exists($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname))){
          $file = new Config($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname), Config::YAML);
          $level = $file->get('Level');
          $x = $file->get('X');
          $y = $file->get('Y');
          $z = $file->get('Z');
          $this->plugin->getServer()->loadLevel($level);
          $s->teleport($this->plugin->getServer()->getLevelByName($level)->getBlockAt($x, $y, $z));
          $s->sendMessage('§aYou have been warped');
        } else {
          $s->sendMessage('§cThis warp doesnt exist. Do /warps for a list of all warps');
        }
      } else {
        $s->sendMessage('§cUsage: /warp [warpname]');
      }
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
