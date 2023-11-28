<?php

/*
 *
 *  ____           _            __           _____
 * |  _ \    ___  (_)  _ __    / _|  _   _  |_   _|   ___    __ _   _ __ ___
 * | |_) |  / _ \ | | | '_ \  | |_  | | | |   | |    / _ \  / _` | | '_ ` _ \
 * |  _ <  |  __/ | | | | | | |  _| | |_| |   | |   |  __/ | (_| | | | | | | |
 * |_| \_\  \___| |_| |_| |_| |_|    \__, |   |_|    \___|  \__,_| |_| |_| |_|
 *                                   |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ReinfyTeam
 * @link https://github.com/ReinfyTeam/
 *
 *
 */

declare(strict_types=1);

namespace Zuri;

use pocketmine\block\BlockTypeIds;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Zuri\Modules\Antibot;
use Zuri\Modules\AutoClicker;
use Zuri\Modules\BadPackets;
use Zuri\Modules\Cheststealer;
use Zuri\Modules\EditionFaker;
use Zuri\Modules\Flight;
use Zuri\Modules\Glitch;
use Zuri\Modules\HighJump;
use Zuri\Modules\Instabreak;
use Zuri\Modules\Killaura;
use Zuri\Modules\NoClip;
use Zuri\Modules\NoPacket;
use Zuri\Modules\Proxy;
use Zuri\Modules\Reach;
use Zuri\Modules\Scaffold;
use Zuri\Modules\Speed;
use Zuri\Modules\Timer;

use function base64_encode;
use function in_array;
use function is_string;
use function microtime;

abstract class Zuri {
	public const PREFIX = TF::GRAY . "(" . TF::YELLOW . TF::BOLD . "Zuri" . TF::RESET . TF::GRAY . ")";
	public const ARROW = TF::RESET . TF::AQUA . TF::BOLD . "»" . TF::RESET;

	public static bool $enabled = true;
	public static array $enabledModules = [];

	// Errors: Simply do like hive.
	public const BADPACKET_HACK = "Bad packet recieved.";
	public const NOPACKET_HACK = "No packet recieved.";
	public const AUTOCLICKER_HACK = "Too high packet recieved.";
	public const NOCLIP_HACK = "Invalid block actor packet.";
	public const VELOCITY_HACK = "Velocity knockback detected.";
	public const TIMER_HACK = "Bad player ticks recieved.";
	public const REACH_HACK = "Invalid entity distance hit recieved.";
	public const KILLAURA_HACK = "Invalid hit registry recieved.";
	public const GLITCH_HACK = "Invalid packet.";
	public const FLIGHT_HACK = "Unexpected movement location packet recieved.";
	public const INSTABREAK_HACK = "Packet recieved miss-match.";
	public const HIGHJUMP_HACK = "Unexpected movement packet recieved.";
	public const EDITIONFAKER_HACK = "Unexpected Game Edition.";
	public const SPEED_HACK = "Invalid movement recieved.";
	public const PROXY_HACK = "Invalid ip address.";
	public const XRAY_HACK = "Invalid visual packet recieved.";
	public const ANTIBOT_HACK = "Invalid player data.";
	public const CHESTSTEALER_HACK = "Inventory moved too fast.";
	public const SCAFFOLD_HACK = "Invalid block position.";

	public const REACH = 0;
	public const SPEED = 1;
	public const AUTOCLICKER = 2;
	public const NOCLIP = 3;
	public const VELOCITY = 4;
	public const TIMER = 5;
	public const KILLAURA = 6;
	public const GLITCH = 7;
	public const FLIGHT = 8;
	public const INSTABREAK = 9;
	public const BADPACKET = 10;
	public const NOPACKET = 11;
	public const HIGHJUMP = 12;
	public const EDITION_FAKER = 13;
	public const XRAY = 14;
	public const PROXY = 15;
	public const SCAFFOLD = 16;
	public const CHESTSTEALER = 17;
	public const ANTIBOT = 18;

	private int $flag;

	public function __construct(int $flag_id) {
		$this->flag = $flag_id;
	}

	public function getFlagId() : int {
		return $this->flag;
	}

	public array $failed = [];
	public array $lastFail = [];

	public function kick(Player $player, string $reason) : void {
		$player->kick(TF::GRAY . "Error: " . base64_encode($reason) . "==", Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $reason);
	}

	public function fail(Player $player, int $howmany = 1) : void {
		if (!isset($this->failed[$player->getUniqueId()->__toString()][$this->flag])) {
			$this->failed[$player->getUniqueId()->__toString()][$this->flag] = $howmany;
		}
		if ($this->failed[$player->getUniqueId()->__toString()][$this->flag] > $this->getMaxViolation()) {
			unset($this->failed[$player->getUniqueId()->__toString()][$this->flag]);
			$this->failed[$player->getUniqueId()->__toString()][$this->flag] = 0;
			$this->notifyAdmins($player, true);
			Anticheat::getInstance()->getServer()->getLogger()->info(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player->getName() . " is kicked for suspected using " . $this->typeIdToString($this->flag) . "!");
			$this->kick($player, $this->typetoReasonString($this->flag));
		} else {
			$this->notifyAdmins($player, false);
			Anticheat::getInstance()->getServer()->getLogger()->info(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player->getName() . " is suspected using " . $this->typeIdToString($this->flag) . "!");
			$this->failed[$player->getUniqueId()->__toString()][$this->flag] += $howmany;
			if (!isset($this->lastFail[$player->getUniqueId()->__toString()][$this->flag])) {
				$this->lastFail[$player->getUniqueId()->__toString()][$this->flag] = microtime(true);
			}
		}
	}

	public function reward(Player $player, float $amount = 1) : void {
		if (!isset($this->failed[$player->getUniqueId()->__toString()][$this->flag])) {
			$this->failed[$player->getUniqueId()->__toString()][$this->flag] = 0;
		}

		if ($this->failed[$player->getUniqueId()->__toString()][$this->flag] === 0) {
			return;
		}

		$this->failed[$player->getUniqueId()->__toString()][$this->flag] -= $amount;
	}

	public function notifyAdmins(Player|string $player, bool $punish = false) : void {
		foreach (Anticheat::getInstance()->getServer()->getOnlinePlayers() as $staff) {
			if ($this->canBypass($staff)) {
				if (is_string($player)) {
					if ($punish) {
						$staff->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player . " is kicked for suspected using " . $this->typeIdToString($this->flag) . "!");
					} else {
						$staff->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player . " is suspected using " . $this->typeIdToString($this->flag) . "!");
					}
				} else {
					if ($punish) {
						$staff->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player->getName() . " is kicked for suspected using " . $this->typeIdToString($this->flag) . "!");
					} else {
						$staff->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . $player->getName() . " is suspected using " . $this->typeIdToString($this->flag) . "!");
					}
				}
				
				if(Anticheat::getInstance()->getConfig()->get("discord-webhook") === true){
					if($punish){
						if(is_string($player) && ($p = Anticheat::getInstance()->getServer()->getPlayerExact($player) !== null)){
							Anticheat::getInstance()->webhook->sendEmbed($p->getName(), $this->typeIdToString($this->flag), Webhook::PLAYER_WARNING);
						} else {
							Anticheat::getInstance()->webhook->sendEmbed($player->getName(), $this->typeIdToString($this->flag), Webhook::PLAYER_WARNING);
						}
					} else {
						if(is_string($player) && ($p = Anticheat::getInstance()->getServer()->getPlayerExact($player) !== null)){
							Anticheat::getInstance()->webhook->sendEmbed($p->getName(), $this->typeIdToString($this->flag), Webhook::PLAYER_KICK);
						} else {
							Anticheat::getInstance()->webhook->sendEmbed($player->getName(), $this->typeIdToString($this->flag), Webhook::PLAYER_KICK);
						}
					}
					
				};
			}
		}
	}

	public function getMaxViolation() : int {
		return Anticheat::getInstance()->getConfig()->get("max-violation", 20);
	}

	public function typeIdToString(int $flag) : string {
		switch($flag) {
			case Zuri::REACH:
				return "Reach";
				break;
			case Zuri::SPEED:
				return "Speed";
				break;
			case Zuri::AUTOCLICKER:
				return "AutoClicker";
				break;
			case Zuri::NOCLIP:
				return "NoClip or Phase";
				break;
			case Zuri::VELOCITY:
				return "Velocity";
				break;
			case Zuri::TIMER:
				return "Timer";
				break;
			case Zuri::KILLAURA:
				return "Killaura";
				break;
			case Zuri::GLITCH:
				return "Glitch or Bugging";
				break;
			case Zuri::FLIGHT:
				return "Flight or Flying";
				break;
			case Zuri::INSTABREAK:
				return "Instabreak or Nuke";
				break;
			case Zuri::BADPACKET:
				return "Bad Packets";
				break;
			case Zuri::NOPACKET:
				return "No Packet or Blink";
				break;
			case Zuri::HIGHJUMP:
				return "HighJump";
				break;
			case Zuri::EDITION_FAKER:
				return "Edition Faker";
				break;
			case Zuri::PROXY:
				return "Proxy/VPN";
				break;
			case Zuri::XRAY:
				return "Xray";
				break;
			case Zuri::ANTIBOT:
				return "Antibot";
				break;
			case Zuri::CHESTSTEALER:
				return "Cheststealer";
				break;
			case Zuri::SCAFFOLD:
				return "Scaffold";
				break;
		}
	}

	public function typeToReasonString(int $flag) : string {
		switch($flag) {
			case Zuri::REACH:
				return Zuri::REACH_HACK;
				break;
			case Zuri::SPEED:
				return Zuri::SPEED_HACK;
				break;
			case Zuri::AUTOCLICKER:
				return Zuri::AUTOCLICKER_HACK;
				break;
			case Zuri::NOCLIP:
				return Zuri::NOCLIP_HACK;
				break;
			case Zuri::VELOCITY:
				return Zuri::VELOCITY_HACK;
				break;
			case Zuri::TIMER:
				return Zuri::TIMER_HACK;
				break;
			case Zuri::KILLAURA:
				return Zuri::KILLAURA_HACK;
				break;
			case Zuri::GLITCH:
				return Zuri::GLITCH_HACK;
				break;
			case Zuri::FLIGHT:
				return Zuri::FLIGHT_HACK;
				break;
			case Zuri::INSTABREAK:
				return Zuri::INSTABREAK_HACK;
				break;
			case Zuri::BADPACKET:
				return Zuri::BADPACKET_HACK;
				break;
			case Zuri::NOPACKET:
				return Zuri::NOPACKET_HACK;
				break;
			case Zuri::HIGHJUMP:
				return Zuri::HIGHJUMP_HACK;
				break;
			case Zuri::EDITION_FAKER:
				return Zuri::EDITIONFAKER_HACK;
				break;
			case Zuri::PROXY:
				return Zuri::PROXY_HACK;
				break;
			case Zuri::XRAY:
				return Zuri::XRAY_HACK;
				break;
			case Zuri::SCAFFOLD:
				return Zuri::SCAFFOLD_HACK;
				break;
			case Zuri::ANTIBOT:
				return Zuri::ANTIBOT_HACK;
				break;
			case Zuri::CHESTSTEALER:
				return Zuri::CHESTSTEALER_HACK;
				break;
		}
	}

	public static function areAllBlocksAboveAir(Player $player) : bool {
		$level = $player->getWorld();
		$posX = $player->getPosition()->x;
		$posY = $player->getPosition()->y + 2;
		$posZ = $player->getPosition()->z;

		// loop through 3x3 square above player head to check for any non-air blocks
		for ($xidx = $posX - 1; $xidx <= $posX + 1; $xidx = $xidx + 1) {
			for ($zidx = $posZ - 1; $zidx <= $posZ + 1; $zidx = $zidx + 1) {
				$pos = new Vector3($xidx, $posY, $zidx);
				$block = $level->getBlock($pos)->getTypeId();
				if ($block != BlockTypeIds::AIR) {
					return false;
				}
			}
		}
		return true;
	}

	public static function getCurrentFrictionFactor(Player $player) {
		$level = $player->getWorld();
		$posX = $player->getPosition()->x;
		$posY = $player->getPosition()->y - 1; #define position of block below player
		$posZ = $player->getPosition()->z;
		$frictionFactor = $level->getBlock(new Vector3($posX, $posY, $posZ))->getFrictionFactor(); # get friction factor from block
		for ($xidx = $posX - 1; $xidx <= $posX + 1; $xidx = $xidx + 1) {
			for ($zidx = $posZ - 1; $zidx <= $posZ + 1; $zidx = $zidx + 1) {
				$pos = new Vector3($xidx, $posY, $zidx);
				if ($level->getBlock($pos)->getTypeId() != BlockTypeIds::AIR) { # only use friction factor if block below isn't air
					if ($frictionFactor <= $level->getBlock($pos)->getFrictionFactor()) { # use new friction factor only if it has a higher value
						$frictionFactor = $level->getBlock($pos)->getFrictionFactor();
					} else { # use block that is two blocks below otherwise
						$pos->y = ($player->getPosition()->y - 2);
						if ($frictionFactor <= $level->getBlock($pos)->getFrictionFactor()) {
							$frictionFactor = $level->getBlock($pos)->getFrictionFactor();
						}
					}
				}
			}
		}
		return $frictionFactor;
	}

	public static function load() : void {
		// load all available check class
		// some checks are aren't done
		foreach ([
			new AutoClicker(),
			new Reach(),
			new NoClip(),
			new Instabreak(),
			new NoPacket(),
			new BadPackets(),
			new EditionFaker(),
			new Timer(),
			new Killaura(),
			new Glitch(),
			new Flight(),
			new BadPackets(),
			new Speed(),
			new Proxy(),
			new HighJump(),
			//new Xray(),
			new Scaffold(),
			new Antibot(),
			new Cheststealer(),
		] as $module) {
			$module->register($module);
			Anticheat::getInstance()->getServer()->getLogger()->info(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::GREEN . "Enabled \"" . $module->typeIdToString($module->getFlagId()) . "\" module!");
		}
	}

	public static function XZDistanceSquared(Vector3 $v1, Vector3 $v2) : float {
		return ($v1->x - $v2->x) ** 2 + ($v1->z - $v2->z) ** 2;
	}

	public function register(Zuri $module) : void {
		Anticheat::getInstance()->getServer()->getPluginManager()->registerEvents($module, Anticheat::getInstance());
		Anticheat::getInstance()->getServer()->getLogger()->debug(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::GREEN . "Registered \"" . $module->typeIdToString($module->getFlagId()) . "\" module!");
		Zuri::$enabledModules[] = $module;
	}

	public function canBypass(Player $p) : bool {
		return (Anticheat::getInstance()->getServer()->isOp($p->getName()) || $p->hasPermission(Anticheat::getInstance()->getConfig()->get("bypass-permission", "zuri.bypass")) || in_array($p->getName(), Anticheat::getInstance()->getBypassConfig()->get("bypassed-players"), true) || !Zuri::$enabled);
	}
}
