<?php

namespace awzaw\notell;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

    private $enabled;

    public function onEnable() {
        $this->enabled = [];
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args) {

        if (strtolower($cmd->getName()) !== "notell")
            return;

        if (!(isset($args[0])) && ($issuer instanceof Player)) {
            if (isset($this->enabled[strtolower($issuer->getName())])) {
                unset($this->enabled[strtolower($issuer->getName())]);
            } else {
                $this->enabled[strtolower($issuer->getName())] = strtolower($issuer->getName());
            }

            if (isset($this->enabled[strtolower($issuer->getName())])) {
                $issuer->sendMessage(TEXTFORMAT::GREEN . "You have turned off viewing /tell messages");
            } else {
                $issuer->sendMessage(TEXTFORMAT::GREEN . "You have turned on viewing /tell messages");
            }
            return true;
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     *
     * @priority MONITOR
     */
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {

        $message = $event->getMessage();
        if (strtolower(substr($message, 0, 5) === "/tell") || strtolower(substr($message, 0, 4) === "/msg")) { //Command
            $command = substr($message, 1);
            $args = explode(" ", $command);
            if (!isset($args[1])) {
                return true;
            }
            $sender = $event->getPlayer();

            foreach ($this->enabled as $noteller) {

                if (strpos(strtolower($noteller), strtolower($args[1])) !== false) {
                    $sender->sendMessage(TextFormat::RED . "This player is not accepting /tell or /msg");
                    $event->setCancelled(true);
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $e) {
        if (isset($this->enabled[strtolower($e->getPlayer()->getName())])) {
            unset($this->enabled[strtolower($e->getPlayer()->getName())]);
        }
    }

}
