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

namespace Zuri\Modules;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEvent;
use pocketmine\player\Player;
use Zuri\Anticheat;
use Zuri\Zuri;
use function array_filter;
use function array_pop;
use function array_unshift;
use function count;
use function microtime;
use function rand;
use function round;

class AutoClicker extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::AUTOCLICKER);
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $ev) : void {
		$player = $ev->getOrigin()->getPlayer();
		$packet = $ev->getPacket();
		if ($player !== null && $player->isOnline()) {
			if ($this->canBypass($player)) {
				return;
			}
			if ($packet instanceof LevelSoundEventPacket) {
				if ($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
					$this->addCps($player);

					if ($this->getCps($player) > Anticheat::getInstance()->getConfig()->get("max-clicks", 30)) {
						$this->fail($player);
					} else {
						$this->reward($player, rand(0.001, 0.03));
					}
				}
			}

			if ($packet instanceof InventoryTransactionPacket) {
				if ($packet->trData instanceof UseItemOnEntityTransactionData) {
					$this->addCps($player);

					if ($this->getCps($player) > Anticheat::getInstance()->getConfig()->get("max-clicks", 30)) {
						$this->fail($player);
					} else {
						$this->reward($player, rand(0.001, 0.03));
					}
				}
			}
		}
	}



	public array $clicks = [];

	public function addCps(Player $player) : void {
		if (empty($this->clicks[$player->getUniqueId()->getBytes()])) {
			$this->clicks[$player->getUniqueId()->getBytes()] = [];
		}
		array_unshift($this->clicks[$player->getUniqueId()->getBytes()], microtime(true));
		if (count($this->clicks[$player->getUniqueId()->getBytes()]) >= 100) {
			array_pop($this->clicks[$player->getUniqueId()->getBytes()]);
		}
	}

	public function getCps(Player $player) : float {
		if (empty($this->clicks[$player->getUniqueId()->getBytes()])) {
			return 0.0;
		}
		$ct = microtime(true);
		return round(count(array_filter($this->clicks[$player->getUniqueId()->getBytes()], static function(float $t) use ($ct) : bool {
			return ($ct - $t) <= 1.0;
		})) / 1.0, 1);
	}
}
