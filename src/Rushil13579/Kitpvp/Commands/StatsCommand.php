<?php

namespace Rushil13579\Kitpvp\Commands;

use pocketmine\Player;

use pocketmine\command\{Command, CommandSender};

use pocketmine\utils\Config;

use Rushil13579\Kitpvp\Kitpvp;

class StatsCommand extends Command {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;

    parent::__construct('stats', 'Check the game stats of a player');
  }

  public function execute(CommandSender $s, string $commandLabel, array $args){
    if($s instanceof Player){
      if(!isset($args[0])){
        if(file_exists($this->plugin->getDataFolder() . 'Stats/' . strtolower($s->getName()))){
          $file = new Config($this->plugin->getDataFolder() . 'Stats/' . strtolower($s->getName()), Config::YAML);
          $kills = $file->get('Kills');
          $deaths = $file->get('Deaths');
          $msg = str_replace(['{kills}', '{deaths}'], [$kills, $deaths], $this->plugin->cfg->get('self-stats-format'));
          $s->sendMessage($msg);
        } else {
          $s->sendMessage('§cYou don\'t have any stats yet');
        }
      } else {
        if($this->plugin->getServer()->getPlayer($args[0])){
          $p = $this->plugin->getServer()->getPlayer($args[0]);
          if(file_exists($this->plugin->getDataFolder() . 'Stats/' . strtolower($p->getName()))){
            $file = new Config($this->plugin->getDataFolder() . 'Stats/' . strtolower($p->getName()), Config::YAML);
            $kills = $file->get('Kills');
            $deaths = $file->get('Deaths');
            $msg = str_replace(['{player}', '{kills}', '{deaths}'], [$p->getName(), $kills, $deaths], $this->plugin->cfg->get('other-stats-format'));
            $s->sendMessage($msg);
          } else {
            $s->sendMessage('§cThis player doesn\'t have any stats');
          }
        } else {
          $p = $args[0];
          if(file_exists($this->plugin->getDataFolder() . 'Stats/' . strtolower($p))){
            $file = new Config($this->plugin->getDataFolder() . 'Stats/' . strtolower($p));
            $kills = $file->get('Kills');
            $deaths = $file->get('Deaths');
            $msg = str_replace(["{player}", "{kills}", "{deaths}"], [$p, $kills, $deaths], $this->plugin->cfg->get('other-stats-format'));
            $s->sendMessage($msg);
          } else {
            $s->sendMessage('§cPlayer not found');
          }
        }
      }
    } else {
      $s->sendMessage($this->plugin->cfg->get('not-player-msg'));
    }
  }
}
