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

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use Zuri\Zuri;
use function abs;
use function pow;
use function sqrt;

class Scaffold extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::SCAFFOLD);
	}

	public function ScaffoldV1(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();

		$block = $event->getBlockAgainst();
		$posBlock = $block->getPosition();
		$itemHand = $player->getInventory()->getItemInHand();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player) return;
		if ($itemHand->getBlock() === VanillaBlocks::AIR()) {
			$x = $posBlock->getX();
			$y = $posBlock->getY();
			$z = $posBlock->getZ();
			if ($x > 1.0 || $z > 1.0 || $y > 1.0) {
				$this->fail($player);
			}
		}
	}

	public function ScaffoldV2(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();

		$pitch = abs($player->getLocation()->getPitch());
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player) return;
		if (
			$pitch > 90 &&
			$event->getBlockAgainst()->getPosition()->getY() < $player->getLocation()->getY() &&
			$player->getNetworkSession()->getPing() < 200
		) {
			$this->fail($player);
		}
	}

	public function ScaffoldV3(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();

		$block = $event->getBlockAgainst();
		$posBlock = $block->getPosition();
		$posPlayer = $player->getLocation();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player) return;
		$distance = Scaffold::distance($posPlayer->asVector3(), $posBlock->asVector3());
		// check first if the player is on sky
		//if (!$event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, 2, 0))->isSolid() && !$event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, -2, 0))->isSolid() && $event->getPlayer()->getGamemode() !== GameMode::SPECTATOR()) return;

		// impossible locations
		//if ($distance < 4 && round($posPlayer->getPitch()) > 10) {
		//	$this->fail($player);
		//}
		// debugging purposes:
		//$player->sendMessage("distance: $distance, yaw: " . $player->getLocation()->getYaw() . ", pitch: " . abs($posPlayer->getPitch()));
	}

	public function ScaffoldV4(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player) return;
		if ($player->getInventory()->getItemInHand()->isNull()) {
			$this->fail($player);
		}
	}

	public static function distance(Vector3 $from, Vector3 $to) {
		return sqrt(pow($from->getX() - $to->getX(), 2) + pow($from->getY() - $to->getY(), 2) + pow($from->getZ() - $to->getZ(), 2));
	}
}
