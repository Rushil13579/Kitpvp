<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;
use pocketmine\command\{Command, CommandSender};
use Rushil13579\Kitpvp\Kitpvp;

class KitCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('kit', 'Kit Command');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      if(isset($args[0])){
        $kitname = $args[0];
        if(isset($this->plugin->kits->get('kits')[$kitname])){
          $this->plugin->addKit($s, $kitname);
        } else {
          $s->sendMessage('§cThis kit does not exist. Do /kits for a list of all available kits');
        }
      } else {
        $this->plugin->kitForm($s);
      }
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
