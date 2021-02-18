<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;

use pocketmine\command\{Command, CommandSender};

use pocketmine\utils\Config;

use Rushil13579\Kitpvp\Kitpvp;

class AddwarpCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('addwarp', 'Add a new warp');

    $this->setPermission('kitpvp.command.addwarp');
    $this->setPermissionMessage('§cYou do not have permission to use this command');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      if(!$s->hasPermission('kitpvp.command.addwarp')){
        $s->sendMessage('§cYou do not have permission to use this command');
        return true;
      }

      if(isset($args[0])){
        $warpname = $args[0];
        if($warpname == '.' or $warpname == '..'){
          $s->sendMessage('§cError, please try again');
          return true;
        }
        if(!file_exists($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname))){
          $file = new Config($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname), Config::YAML, array(
            'Level' => $s->getLevel()->getName(), 'X' => $s->getX(), 'Y' => $s->getY(), 'Z' => $s->getZ()
          ));
          $s->sendMessage("§aYou have created warp §6$warpname");
        } else {
          $s->sendMessage('§cThis warp already exists');
        }
      } else {
        $s->sendMessage('§cUsage: /addwarp [warpname]');
      }
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
