<?php

namespace HiroTeam\drawer;

#Drawer plugin by HiroTeam | Plugin de Drawer par la HiroTeam
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝

use HiroTeam\drawer\forms\CustomForm;
use pocketmine\item\Item;
use pocketmine\Player;

class DrawerUI
{
    private static $itemtarget = [];
    private static $x;
    private static $y;
    private static $z;
    private static $level;
    public static function TakeInDrawerUI(Player $player, $x, $y, $z, $level){
        $form = self::createCustomForm(function (Player $player, array $data = null) {
            $result = $data[0];
            if ($result === null) {
                unset(self::$itemtarget[$player->getName()]);
                unset(self::$x[$player->getName()]);
                unset(self::$y[$player->getName()]);
                unset(self::$z[$player->getName()]);
                unset(self::$level[$player->getName()]);
                return true;
            }
            $player->getInventory()->addItem(Item::get(self::$itemtarget[$player->getName()][0], self::$itemtarget[$player->getName()][1], $data[1]));
            Drawer::getMainInstance()->subtractAmountbyCo($data[1], self::$x[$player->getName()], self::$y[$player->getName()], self::$z[$player->getName()],self::$level[$player->getName()]);
            $player->sendMessage(Drawer::getMainInstance()->getMessage(Drawer::getMainInstance()->config->get("succestake"), Item::get(self::$itemtarget[$player->getName()][0], self::$itemtarget[$player->getName()][1])->getName(), $data[1]));
            unset(self::$itemtarget[$player->getName()]);
            unset(self::$x[$player->getName()]);
            unset(self::$y[$player->getName()]);
            unset(self::$z[$player->getName()]);
            unset(self::$level[$player->getName()]);
        });
        $item = Drawer::getMainInstance()->getItembyCo($x, $y, $z, $level);
        $ItemName = explode(":", $item);
        $list[] = Item::get($ItemName[0], $ItemName[1])->getName();
        self::$itemtarget[$player->getName()] = $ItemName;
        self::$x[$player->getName()] = $x;
        self::$y[$player->getName()] = $y;
        self::$z[$player->getName()] = $z;
        self::$level[$player->getName()] = $level;
        $form -> setTitle(Drawer::getMainInstance()->config->get("TakeUIInDrawerTitle"));
        $form -> addDropdown(Drawer::getMainInstance()->getMessage(Drawer::getMainInstance()->config->get("TakeUIText"), Item::get($ItemName[0], $ItemName[1])->getName(), Drawer::getMainInstance()->getItemAmountbyCo($x, $y, $z, $level)), $list);
        $form -> addSlider(Drawer::getMainInstance()->config-> get("TakeUISlider"), 0, Drawer::getMainInstance()->CountPlayerItem($ItemName, $player, $x, $y, $z, $level), 1);
        $form -> sendToPlayer($player);
        return $form;
    }
    #thanks jojoe77777
    public static function createCustomForm(callable $function = null) : CustomForm {
        return new CustomForm($function);
    }

}