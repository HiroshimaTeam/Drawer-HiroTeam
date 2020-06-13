<?php

namespace HiroTeam\drawer;

#Drawer plugin by HiroTeam | Plugin de Drawer par la HiroTeam
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class DrawerListener implements Listener
{
    private $main;
    public function __construct(Drawer $main){
        $this->main = $main;
    }
    public function PlaceBlock(BlockPlaceEvent $event): void{
        $block = $event->getBlock();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
        $level = $block->getLevel()->getName();
        if($this->main->config->get("DrawerBlock") === $block->getId() . ":" . $block->getDamage()){
            $this->main->CreateDrawerbyCo($x, $y, $z, $level);
        }
    }
    public function onBreak(BlockBreakEvent $event): void{
        $block = $event->getBlock();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
        $level = $block->getLevel()->getName();
        if($this->main->config->get("DrawerBlock") === $block->getId() . ":" . $block->getDamage()){
            if($this->main->getItembyCo($x, $y, $z, $level) != NULL or $this->main->getItemAmountbyCo($x, $y, $z, $level) != 0) {
                $item = $this -> main -> getItembyCo($x, $y, $z, $level);
                $ItemName = explode(":", $item);
                $amount = $this -> main -> getItemAmountbyCo($x, $y, $z, $level);
                $MaxStackSize = Item::get($ItemName[0], $ItemName[1])->getMaxStackSize();
                $i = 0;
                $arrayItem = [];
                if($this->main->config->get("LoseItemOnBreak") === true) {
                    while ($i >= 0) {
                        $amount = $amount - $MaxStackSize;
                        if ($amount > $MaxStackSize * $i) {
                            $input = Item ::get($ItemName[0], $ItemName[1], $MaxStackSize);
                            array_push($arrayItem, $input);
                            $i++;
                        } else {
                            $input = Item ::get($ItemName[0], $ItemName[1], $amount);
                            array_push($arrayItem, $input);
                            $i = -1;
                        }
                    }
                }
                if($this->main->config->get("LoseItemOnBreak") === false) {
                    while ($i >= 0) {
                        if ($amount > $MaxStackSize * $i) {
                            $amount = $amount - $MaxStackSize;
                            $input = Item ::get($ItemName[0], $ItemName[1], $MaxStackSize);
                            array_push($arrayItem, $input);
                            $i++;
                        } else {
                            $input = Item ::get($ItemName[0], $ItemName[1], $amount);
                            array_push($arrayItem, $input);
                            $i = -1;
                        }
                    }
                }
                $drawer = $this->main->config->get("DrawerBlock");
                $DrawerName = explode(":", $drawer);
                $input = Item::get($DrawerName[0], $DrawerName[1], 1);
                array_push($arrayItem, $input);
                $event->setDrops($arrayItem);
                }
            $this->main->deleteDrawerbyCo($x, $y, $z, $level);
        }
    }
    public function onExplode(EntityExplodeEvent $event): void{
        $listBlock = $event->getBlockList();
        foreach ($listBlock as $block) {
            if ($this->main->config->get("DrawerBlock") === $block->getId() . ":" . $block->getDamage()) {
                $x = $block->x;
                $y = $block->y;
                $z = $block->z;
                $level = $block->getLevel()->getName();
                $this->main->deleteDrawerbyCo($x, $y, $z, $level);
            }
        }
    }
    public function onTouch(PlayerInteractEvent $event): void{
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        $amount = $player->getInventory()->getItemInHand()->getCount();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
        $level = $block->getLevel()->getName();
        if($this->main->config->get("DrawerBlock") === $block->getId() . ":" . $block->getDamage()){
            $event->setCancelled(true);
            if(!$player->isSneaking()){
                if($item->getId() === 0){
                    $player->sendPopup($this->main->config->get("takedrawer"));
                    return;
                }
                if($this->main->getItembyCo($x, $y, $z, $level) === NULL or $this->main->getItemAmountbyCo($x, $y, $z, $level) === 0){
                    $this->main->inPutItembyCo($item, $x, $y, $z, $level);
                    $this->main->addAmountbyCo($amount, $x, $y, $z, $level);
                    $player->getInventory()->removeItem(Item::get($item->getId(), $item->getDamage(), $amount));
                    $player->sendPopup($this->main->getMessage($this->main->config->get("SuccesPutAndUpdate"), $item->getName(), $this->main->getItemAmountbyCo($x, $y, $z, $level)));
                    return;
                }elseif($this->main->getItembyCo($x, $y, $z, $level) === $item->getId() . ":" . $item->getDamage()){
                    if(!$this->main->ReachAddLimitbyCo($amount, $x, $y, $z, $level)){
                        $amount = $this->main->config->get("ItemLimit") - $this->main->getItemAmountbyCo($x, $y, $z, $level);
                        $this->main->addAmountbyCo($amount, $x, $y, $z, $level);
                        $player->getInventory()->removeItem(Item::get($item->getId(), $item->getDamage(), $amount));
                        $player->sendPopup($this->main->config->get("isFull"));
                        return;
                    }
                    $this->main->addAmountbyCo($amount, $x, $y, $z, $level);
                    $player->getInventory()->removeItem(Item::get($item->getId(), $item->getDamage(), $amount));
                    $player->sendPopup($this->main->getMessage($this->main->config->get("SuccesAdd"), $item->getName(), $this->main->getItemAmountbyCo($x, $y, $z, $level)));
                    return;
                }
                else{
                    $player->sendPopup($this->main->config->get("failedToPut"));
                }
            } else {
                if($this->main->getItembyCo($x, $y, $z, $level) === NULL or $this->main->getItemAmountbyCo($x, $y, $z, $level) === 0){
                    $player->sendPopup($this->main->config->get("emptydrawer"));
                    return;
                }
                DrawerUI::TakeInDrawerUI($player, $x, $y, $z, $level);
            }
        }
    }
}