<?php

namespace Rushil13579\Kitpvp;

use pocketmine\{Player, Server};

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command, CommandSender, ConsoleCommandSender};

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\item\Item;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};

use pocketmine\entity\{Effect, EffectInstance};

use Rushil13579\Kitpvp\Commands\{KitCommand, KitsCommand};
use Rushil13579\Kitpvp\Commands\{WarpCommand, WarpsCommand, AddwarpCommand, DelwarpCommand};
use Rushil13579\Kitpvp\Commands\StatsCommand;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\utils\Config;

use InvalidArgumentException;

class Kitpvp extends PluginBase implements Listener {

  public $cps = [];

  public $items = [];
  public $effects = [];
  public $commands = [];

  private $cfg;
  private $kits;

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

    $this->saveDefaultConfig();
    $this->getResource('config.yml');

    $this->saveResource('kits.yml');

    @mkdir($this->getDataFolder() . 'Warps/');
    @mkdir($this->getDataFolder() . 'Stats/');

    $this->cfg = $this->getConfig();
    $this->kits = new Config($this->getDataFolder() . 'Kits.yml', Config::YAML);

    $this->versionCheck();

    $this->formAPICheck();

    $this->registerCommands();

    $this->loadKits();
  }

  public function versionCheck(){
    if($this->cfg->get('version') != '1.0'){
      $this->getLogger()->notice('§cYour configuration file is outdated. Please delete \'config.yml\' and restart your server to install the latest configuration file');
    }
  }

  public function formAPICheck(){
    if($this->kits->get('kits-enabled') == true){
      if($this->kits->get('kit-form-support') == true){
        if($this->getServer()->getPluginManager()->getPlugin('FormAPI') === null){
          $this->kits->set('kit-form-support', false);
          $this->kits->save();
          $this->getLogger()->notice('§cKit form support Disabled as FormAPI was not found');
        }
      }
    }
  }

  public function registerCommands(){
    $cmdMap = $this->getServer()->getCommandMap();
    $cmdMap->register('starts', new StatsCommand($this));
    if($this->cfg->get('warps-enabled') == true){
      $cmdMap->register('warp', new WarpCommand($this));
      $cmdMap->register('warps', new WarpsCommand($this));
      $cmdMap->register('addwarp', new AddwarpCommand($this));
      $cmdMap->register('delwarp', new DelwarpCommand($this));
    }
    if($this->kits->get('kits-enabled') == true){
      $cmdMap->register('kit', new KitCommand($this));
      $cmdMap->register('kits', new KitsCommand($this));
    }
  }

# ==================== SCORE TAG ====================

  public function setScoreTag($player){
    if($this->cfg->get('score-tag-enabled') == true){
      $tag = str_replace(['{health}', '{ping}'], [round($player->getHealth())/2, $player->getPing()], $this->cfg->get('score-tag-format'));
      $player->setScoreTag($tag);
    }
  }

# ==================== CPS ====================

  public function addCps($player){
    if(!isset($this->cps[$player->getName()])){
      $this->cps[$player->getName()] = [time(), 0];
    }

    $time = $this->cps[$player->getName()][0];
		$cps = $this->cps[$player->getName()][1];

		if($time != time()){
			$time = time();
			$cps = 0;
		}

		$cps++;
		$this->cps[$player->getName()] = [$time, $cps];
  }

  public function getCps($player){
    if(!isset($this->cps[$player->getName()])){
      return 0;
    }
    $time = $this->cps[$player->getName()][0];
    $cps = $this->cps[$player->getName()][1];
    if($time !== time()){
      unset($this->cps[$player->getName()]);
      return 0;
    }
    return (int)$cps;
  }

# ==================== DEATH MESSAGES ====================

  public function deathMessage(EntityDamageEvent $lastdmg = null){
    if(!$lastdmg){
      return $this->cfg->get('default-death-msg');
    }

    switch($lastdmg->getCause()){

      case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
        return $this->cfg->get('kill-death-msg');
      break;

      case EntityDamageEvent::CAUSE_PROJECTILE:
        return $this->cfg->get('projectile-death-msg');
      break;

      case EntityDamageEvent::CAUSE_SUFFOCATION:
        return $this->cfg->get('suffocation-death-msg');
      break;

      case EntityDamageEvent::CAUSE_FALL:
        return $this->cfg->get('fall-death-msg');
      break;

      case EntityDamageEvent::CAUSE_FIRE || EntityDamageEvent::CAUSE_FIRE_TICK:
        return $this->cfg->get('fire-death-msg');
      break;

      case EntityDamageEvent::CAUSE_LAVA:
        return $this->cfg->get('lava-death-msg');
      break;

      case EntityDamageEvent::CAUSE_DROWNING:
        return $this->cfg->get('drown-death-msg');
      break;

      case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION or EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
        return $this->cfg->get('explosion-death-msg');
      break;

      case EntityDamageEvent::CAUSE_VOID:
        return $this->cfg->get('void-death-msg');
      break;

      case EntityDamageEvent::CAUSE_SUICIDE:
        return $this->cfg->get('suicide-death-msg');
      break;

      case EntityDamageEvent::CAUSE_MAGIC:
        return $this->cfg->get('magic-death-msg');
      break;
    }
  }

# ==================== STATS ====================

  public function generateStatsFile($player){
    $file = new Config($this->getDataFolder() . 'Stats/' . strtolower($player->getName()), Config::YAML, array(
      'Kills' => 0, 'Deaths' => 0
    ));
  }

  public function addKill($player){
    if(!file_exists($this->getDataFolder() . 'Stats/' . strtolower($player->getName()))){
      $this->generateStatsFile($player);
      $file = new Config($this->getDataFolder() . 'Stats/' . strtolower($player->getName()), Config::YAML);
      $file->set('Kills', 1);
      $file->save();
    } else {
      $file = new Config($this->getDataFolder() . 'Stats/' . strtolower($player->getName()), Config::YAML);
      $file->set('Kills', $file->get('Kills') + 1);
      $file->save();
    }
  }

  public function addDeath($player){
    if(!file_exists($this->getDataFolder() . 'Stats/' . strtolower($player->getName()))){
      $this->generateStatsFile($player);
      $file = new Config($this->getDataFolder() . 'Stats/' . strtolower($player->getName()), Config::YAML);
      $file->set('Deaths', 1);
      $file->save();
    } else {
      $file = new Config($this->getDataFolder() . 'Stats/' . strtolower($player->getName()), Config::YAML);
      $file->set('Deaths', $file->get('Deaths') + 1);
      $file->save();
    }
  }

# ==================== KITS ====================

# ===== LOADING =====

  public function loadKits(){
    $file = $this->kits->get('kits');
    if($this->kits->get('kits-enabled') == true){
      foreach(array_keys($file) as $kitname){

        if(isset($file[$kitname]['items']) && is_array($file[$kitname]['items'])){
          foreach($file[$kitname]['items'] as $itemData){
            $item = $this->loadItems($itemData);
            if($item != null){
              $this->items[$kitname][] = $item;
            }
          }
        }

        if(isset($file[$kitname]['effects']) && is_array($file[$kitname]['effects'])){
          foreach($file[$kitname]['effects'] as $effectData){
            $effect = $this->loadEffects($effectData);
            if($effect != null){
              $this->effects[$kitname][] = $effect;
            }
          }
        }
      }
    }
  }

  public function loadItems($itemData){
    $array = explode(':', $itemData);
    if(count($array) < 3){
      $this->getLogger()->warning('§cBad configuration in kits.yml. id:meta:amount must be specified');
      return null;
    }

    $id = array_shift($array);
    $damage = array_shift($array);
    $amount = array_shift($array);

    try {
      $item = Item::fromString($id.':'.$damage);
    } catch (InvalidArgumentException $exception){
      $this->getLogger()->warning('§cBad configuration in kits.yml. Invalid id:meta');
      return null;
    }

    if(is_numeric($amount)){
      $item->setCount((int)$amount);
    } else {
      $this->getLogger()->warning('§cBad configuration in kits.yml. Amount isn\'t numeric');
      return null;
    }

    if(!empty($array)){
      $name = array_shift($array);
      if(strtolower($name) != 'default'){
        $item->setCustomName($name);
      }
    }

    if(!empty($array)){
      $enchArray = array_chunk($array, 2);
      foreach($enchArray as $enchData){
        if(count($enchData) != 2){
          $this->getLogger()->warning('§cBad configuration in kits.yml. Enchantments must be in the form enchantment_name:level');
          continue;
        }

        $ench = Enchantment::getEnchantmentByName($enchData[0]);
        if($ench == null){
          $this->getLogger()->warning('§cBad configuration in kits.yml. Enchantment ' . $enchData[0] . ' is invalid');
          continue;
        }

        if(!is_numeric($enchData[1])){
          $this->getLogger()->warning('§cBad configuration in kits.yml. Enchantment level isn\'t numeric');
          continue;
        }

        $item->addEnchantment(new EnchantmentInstance($ench, (int) $enchData[1]));
      }
    }
    return $item;
  }

  public function loadEffects($effectData){
    $array = explode(':', $effectData);
    if(count($array) < 3){
      $this->getLogger()->warning('§cBad configuration in kits.yml. effect:duration:amplifier must be specified');
      return null;
    }

    $name = array_shift($array);
    $duration = array_shift($array);
    $amplifier = array_shift($array);

    if(!is_numeric($duration) or !is_numeric($amplifier)){
      $this->getLogger()->warning('§cBad configuration in kits.yml. Duration & Amplifier must be numeric');
      return null;
    }

    $effectname = Effect::getEffectByName($name);
    if($effectname === null){
      $this->getLogger()->warning('§cBad configuration in kits.yml. Effect ' . $effectData[0] . ' not found');
      return null;
    }

    $effect = new EffectInstance($effectname, (int) $duration * 20, (int) $amplifier);
    return $effect;
  }

# ===== ADDING =====

  public function addKit($player, $kitname){
    if(isset($this->items[$kitname])){
      foreach($this->items[$kitname] as $item){
        $player->getInventory()->addItem($item);
      }
    }

    if(isset($this->effects[$kitname])){
      foreach($this->effects[$kitname] as $effect){
        $player->addEffect(clone $effect);
      }
    }

    if(isset($this->kits->get('kits')[$kitname]['commands']) && is_array($this->kits->get('kits')[$kitname]['commands'])){
      foreach($this->kits->get('kits')[$kitname]['commands'] as $cmd){
        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $cmd));
      }
    }
  }

# ===== FORM =====

  public function kitForm($player){
    $form = new SimpleForm(function (Player $player, $data = null){
      if($data === null){
        return "";
      }

      foreach(array_keys($this->kits->get('kits')) as $kitname){
        if($kitname === $data){
          $this->addKit($player, $kitname);
        }
      }
    });

    $form->setTitle($this->kits->get('kit-form-title'));
    foreach(array_keys($this->kits->get('kits')) as $kitname){
      $form->addButton($this->kits->get('kits')[$kitname]['form-name-format'], '-1', '', $kitname);
    }
    $form->sendToPlayer($player);
    return $form;
  }
}
