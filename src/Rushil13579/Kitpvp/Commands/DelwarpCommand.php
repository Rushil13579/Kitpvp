<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;

use pocketmine\command\{Command, CommandSender};

use pocketmine\utils\Config;

use Rushil13579\Kitpvp\Kitpvp;

class DelwarpCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('delwarp', 'Delete an existing warp');

    $this->setPermission('kitpvp.command.delwarp');
    $this->setPermissionMessage('§cYou do not have permission to use this command');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      if(!$s->testPermission()){
        $s->sendMessage('§cYou do not have permission to use this command');
        return true;
      }

      if(isset($args[0])){
        $warpname = $args[0];
        if($warpname == '.' or $warpname == '..'){
          $s->sendMessage('§cError, please try again');
          return true;
        }
        if(file_exists($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname))){
          unlink($this->plugin->getDataFolder() . 'Warps/' . strtolower($warpname));
          $s->sendMessage("§aYou have deleted §6$warpname §awarp");
        } else {
          $s->sendMessage('§cThis warp doesnt exist');
        }
      } else {
        $s->sendMessage('§cUsage: /delwarp [warpname]');
      }
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
