<?php

namespace WarpPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\math\Vector3;

class Main extends PluginBase {
    private Config $config;

    public function onEnable(): void {
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, ["warps" => []]);
        $this->getLogger()->info("WarpPlugin enabled!");
    }

    public function onDisable(): void {
        $this->getLogger()->info("WarpPlugin disabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return true;
        }

        switch (strtolower($command->getName())) {
            case "setwarp":
                if (!$sender->hasPermission("warpplugin.setwarp")) {
                    $sender->sendMessage("You do not have permission to use this command.");
                    return true;
                }
                
                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /setwarp <name>");
                    return true;
                }
                
                $warpName = strtolower($args[0]);
                $position = $sender->getPosition();
                
                $warps = $this->config->get("warps", []);
                $warps[$warpName] = [
                    "x" => $position->getX(),
                    "y" => $position->getY(),
                    "z" => $position->getZ(),
                    "level" => $sender->getWorld()->getFolderName()
                ];
                
                $this->config->set("warps", $warps);
                $this->config->save();
                $sender->sendMessage("Warp '$warpName' has been set.");
                return true;
            
            case "warp":
                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /warp <name>");
                    return true;
                }
                
                $warpName = strtolower($args[0]);
                $warps = $this->config->get("warps", []);
                
                if (!isset($warps[$warpName])) {
                    $sender->sendMessage("Warp '$warpName' does not exist.");
                    return true;
                }
                
                $warp = $warps[$warpName];
                $world = Server::getInstance()->getWorldManager()->getWorldByName($warp["level"]);
                
                if ($world === null) {
                    $sender->sendMessage("The world for this warp is not loaded.");
                    return true;
                }
                
                $sender->teleport(new Vector3($warp["x"], $warp["y"], $warp["z"]));
                $sender->sendMessage("Teleported to '$warpName'.");
                return true;
            
            case "delwarp":
                if (!$sender->hasPermission("warpplugin.delwarp")) {
                    $sender->sendMessage("You do not have permission to use this command.");
                    return true;
                }
                
                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /delwarp <name>");
                    return true;
                }
                
                $warpName = strtolower($args[0]);
                $warps = $this->config->get("warps", []);
                
                if (!isset($warps[$warpName])) {
                    $sender->sendMessage("Warp '$warpName' does not exist.");
                    return true;
                }
                
                unset($warps[$warpName]);
                $this->config->set("warps", $warps);
                $this->config->save();
                $sender->sendMessage("Warp '$warpName' has been deleted.");
                return true;
        }
        return false;
    }
}
