<?php

namespace Rushil13579\Kitpvp;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerDeathEvent, PlayerExhaustEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityRegainHealthEvent, EntityDamageByEntityEvent};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\entity\Living;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use pocketmine\utils\Config;

use Rushil13579\Kitpvp\Kitpvp;

class EventListener implements Listener {

  private $plugin;

  public function __construct(Kitpvp $plugin){
    $this->plugin = $plugin;
  }

# ==================== PLAYER EVENTS ==================== #

  public function onJoin(PlayerJoinEvent $e){
    $p = $e->getPlayer();
    $msg = str_replace('{player}', $p->getName(), $this->plugin->cfg->get('join-msg'));
    $e->setJoinMessage($msg);

    $this->plugin->setScoreTag($p);
  }

  public function onQuit(PlayerQuitEvent $e){
    $p = $e->getPlayer();
    $msg = str_replace('{player}', $p->getName(), $this->plugin->cfg->get('quit-msg'));
    $e->setQuitMessage($msg);
  }

  public function onExhaust(PlayerExhaustEvent $e){
    $p = $e->getPlayer();
    if(in_array($p->getLevel()->getName(), $this->plugin->cfg->get('no-hunger-worlds'))){
      $e->setCancelled();
    }
  }

  public function onDeath(PlayerDeathEvent $e){
    $p = $e->getPlayer();
    $lastdmg = $p->getLastDamageCause();
    if($lastdmg instanceof EntityDamageByEntityEvent){
    $msg = $this->plugin->deathMessage($lastdmg);
      if($lastdmg->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK or $lastdmg->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
        $damager = $lastdmg->getDamager();
        if($damager instanceof Living){
          $msg = str_replace(['{player}', '{killer}', '{health}', '{maxhealth}'], [$p->getName(), $damager->getName(), $damager->getHealth(), $damager->getMaxHealth()], $msg);
          if($damager instanceof Player){
            $this->plugin->addKill($damager);
            $this->plugin->addDeath($p);
          }
        } else {
          $msg = $this->plugin->cfg->get('default-death-msg');
        }
      } else {
        $msg = str_replace('{player}', $p->getName(), $msg);
      }
    }

    $e->setDeathMessage($msg);
  }

# ==================== ENTITY EVENTS ==================== #

  public function onDamage(EntityDamageEvent $e){
    $entity = $e->getEntity();
    if($entity instanceof Player){

      if($e instanceof EntityDamageByEntityEvent){
        $damager = $e->getDamager();
        if($damager instanceof Player){
          if($this->plugin->cfg->get('cps-popup') == true){
            $cps = $this->plugin->getCps($damager);
            $damager->sendPopup(str_replace('{cps}', $cps, $this->plugin->cfg->get('cps-format')));
          }
        }
      }

      if($e->getCause() === EntityDamageEvent::CAUSE_FALL){
        if(in_array($entity->getLevel()->getName(), $this->plugin->cfg->get('no-fall-dmg-worlds'))){
          $e->setCancelled();
        }
      }

      if($e->getCause() === EntityDamageEvent::CAUSE_VOID){
        if(in_array($entity->getLevel()->getName(), $this->plugin->cfg->get('no-void-dmg-worlds'))){
          $e->setCancelled();
        }
      }

      $this->plugin->setScoreTag($entity);
    }
  }

  public function onRegain(EntityRegainHealthEvent $e){
    $entity = $e->getEntity();
    if($entity instanceof Player){
      $this->plugin->setScoreTag($entity);
    }
  }

# ==================== BLOCK EVENTS ==================== #

  /**
  *@priority HIGHEST
  **/

  public function onBreak(BlockBreakEvent $e){
    $p = $e->getPlayer();
    if(in_array($p->getLevel()->getName(), $this->plugin->cfg->get('no-block-break-worlds'))){
      $e->setCancelled();
    }
  }

  /**
  *@priority HIGHEST
  **/

  public function onPlace(BlockPlaceEvent $e){
    $p = $e->getPlayer();
    if(in_array($p->getLevel()->getName(), $this->plugin->cfg->get('no-block-place-worlds'))){
      $e->setCancelled();
    }
  }

# ==================== SERVER EVENTS ==================== #

  public function onDataPacketReceive(DataPacketReceiveEvent $e){
    $p = $e->getPlayer();
    $packet = $e->getPacket();
    if($packet instanceof InventoryTransactionPacket){
      $transactionType = $packet->transactionType;
      if($transactionType === InventoryTransactionPacket::TYPE_USE_ITEM || $transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
        $this->plugin->addCps($p);
      }
    }
  }
}
