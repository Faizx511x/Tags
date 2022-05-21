<?php

declare(strict_types=1);

namespace FaizDev\Tags;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use ReflectionClass;
use pocketmine\resourcepacks\ZippedResourcePack;

use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Main extends PluginBase implements Listener
{

	public CONST Syntax = ["{NOTICE}", "{JOIN}", "{WARN}", "{INFO}", "{GAME}", "{TEAM}", "{PARTY}", "{PLAYER}", "{STAFF}", "{HELPER}", "{VIP}", "{ULTRA}", "{INFLNCR}"];
	public CONST Unicode = ["", "", "", "", "", "", "", "", "", "", "", "", "", ];
	public function onEnable() : void
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveResource("Tags.mcpack", true);

		$manager = $this->getServer()->getResourcePackManager();
		$pack = new ZippedResourcePack($this->getDataFolder() . "Tags.mcpack");

		$reflection = new ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);

		$currentResourcePacks = $property->getValue($manager);
		$currentResourcePacks[] = $pack;
		$property->setValue($manager, $currentResourcePacks);

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		$currentUUIDPacks[strtolower($pack->getPackId())] = $pack;
		$property->setValue($manager, $currentUUIDPacks);

		$property = $reflection->getProperty("serverForceResources");
		$property->setAccessible(true);
		$property->setValue($manager, true);
	}

	public function onCommandPreProcess(PlayerCommandPreprocessEvent $event)
	{
		$player = $event->getPlayer();
		$message = $event->getMessage();
		if (!$player->hasPermission("tags.use")) {
			foreach (self::Syntax as $syntax) {
			}
			if (strrchr($message, $syntax) == true) {
				$player->sendMessage("{WARN}§c You do not have permission to use the Tags!");
				$event->cancel();
			}
		} else {
			$search = self::Syntax;
			$replace = self::Unicode;
			$subject = $message;
			$result = str_replace($search, $replace, $subject);

			$event->setMessage($result);
		}
	}

	public function onDataPacketSendEvent(DataPacketSendEvent $event) : void
	{
		$pk = $event::getPackets() as $packet;
		if ($pk instanceof TextPacket) {
			$pattern = "/%*(([a-z0-9_]+\.)+[a-z0-9_]+)/i";
			$replacement = "%$1";
			$subject = $pk->message;
			$preg_replace = preg_replace($pattern, $replacement, $pk->message);

			$search = self::Syntax;
			$replace = self::Unicode;
			$subject = $preg_replace;
			$result = str_replace($search, $replace, $subject);

			$pk->message = $result;
		}
	}
}
