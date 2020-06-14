<?php

namespace HiroTeam\drawer;

#Drawer plugin by HiroTeam | Plugin de Drawer par la HiroTeam
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


class Drawer extends PluginBase{

    public $config;
    public $db;
    protected static $instance;

    public function onEnable()
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new DrawerListener($this), $this);
        if (! file_exists($this -> getDataFolder() . "config.yml")) {
            $this -> saveResource("config.yml");
        }
        $this->config = new Config($this -> getDataFolder() . "config.yml", Config::YAML);
        $this->db = new \SQLite3($this->getDataFolder() . "Drawerdb.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS drawer(item TEXT, amount INT, x INT, y INT, z INT, level TEXT);");
    }
    public static function getMainInstance() : self{
        return self::$instance;
    }
    public function getItembyCo($x, $y, $z, $level){
        $result = $this -> db -> query("SELECT item FROM drawer WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $resultArr = $result -> fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return NULL;
        }
        return (string)$resultArr["item"];
    }
    public function getItemAmountbyCo($x, $y, $z, $level): int{
        $result = $this -> db -> query("SELECT amount FROM drawer WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $resultArr = $result -> fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return 0;
        }
        return (int)$resultArr["amount"];
    }
    public function CreateDrawerbyCo($x, $y, $z, $level): void{
        $drawer = $this->db->prepare("INSERT INTO drawer (item, amount, x, y, z, level) VALUES (:item, :amount, :x, :y, :z, :level);");
        $drawer->bindValue(":item", NULL);
        $drawer->bindValue(":amount", NULL);
        $drawer->bindValue(":x", $x);
        $drawer->bindValue(":y", $y);
        $drawer->bindValue(":z", $z);
        $drawer->bindValue(":level", $level);
        $result = $drawer->execute();
    }
    public function deleteDrawerbyCo($x, $y, $z, $level): void{
        $item = $this->db->prepare("DELETE FROM drawer WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $item->execute();
    }
    public function inPutItembyCo(Item $item, $x, $y, $z, $level): void{
        $item = $item->getId() . ":" . $item->getDamage();
        $item2put = $this->db->prepare("UPDATE drawer SET item = '$item' WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $item2put->execute();
    }
    public function addAmountbyCo($amount, $x, $y, $z, $level): void{
        $AddAmount = $amount + $this->getItemAmountbyCo($x, $y, $z, $level);
        $item = $this->db->prepare("UPDATE drawer SET amount = '$AddAmount' WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $item->execute();
    }
    public function subtractAmountbyCo($amount, $x, $y, $z, $level): void{
        $subtractAmount = $this->getItemAmountbyCo($x, $y, $z, $level) - $amount;
        $item = $this->db->prepare("UPDATE drawer SET amount = '$subtractAmount' WHERE x = '$x' AND y = '$y' AND z = '$z' AND level = '$level';");
        $item->execute();
    }
    public function ReachAddLimitbyCo($amount, $x, $y, $z, $level): bool{
        $AddAmount = $amount + $this->getItemAmountbyCo($x, $y, $z, $level);
        if($AddAmount > $this->config->get("ItemLimit")){
            return false;
        }
        return true;
    }
    public function getMessage($message, $itemname, $amount): string {
        $message = str_replace("{item}", $itemname, $message);
        $message = str_replace("{amount}", $amount, $message);
        $message = str_replace("{limit}", $this->config->get("ItemLimit"), $message);
        return $message;
    }
    public function CountPlayerItem(array $item, Player $player, $x, $y, $z, $level): int
    {
        $result = Item::get($item[0], $item[1]);
        $maxStackSize = $result->getMaxStackSize();
        $inventory = $player -> getInventory();
        $number = 0;
        $slots = 36;
        $nombreslotItem = 0;
        foreach ($inventory -> getContents() as $slot => $item) {
            if (isset($item)){
                $slots--;
            }
            if ($item -> getId() === $result -> getId() and $item -> getDamage() === $result -> getDamage()) {
                $number = $number + $item -> getCount();
                $nombreslotItem++;
            }
        }
        $numberFinal = $maxStackSize * $nombreslotItem - $number;
        $totalslots = $slots * $maxStackSize;
        $inventaire = $numberFinal + $totalslots;
      
        $nombreTotal =  $this->getItemAmountbyCo($x, $y, $z, $level);

        if($inventaire <= $nombreTotal){
            return $inventaire;
        }
        else{
            return $nombreTotal;
        }
    }
}
