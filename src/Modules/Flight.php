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

use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\UpdateAdventureSettingsPacket;
use pocketmine\player\Player;
use Zuri\Zuri;
use function in_array;
use function intval;

class Flight extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::FLIGHT);
	}

	// for most advance clients, manipulating fly packets
	public function flightV1(DataPacketReceiveEvent $event) {
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if ($player === null) {
			return;
		}
		if ($this->canBypass($player)) {
			return;
		}
		if ($packet instanceof UpdateAdventureSettingsPacket) {
			if (!$player->isCreative() && !$player->isSpectator() && !$player->getAllowFlight()) {
				switch ($packet->flags) {
					case 614:
					case 615:
					case 103:
					case 102:
					case 38:
					case 39:
						$event->cancel();
						$this->fail($player);
						break;
				}
				if ((($packet->flags >> 9) & 0x01 === 1) || (($packet->flags >> 7) & 0x01 === 1) || (($packet->flags >> 6) & 0x01 === 1)) {
					$this->fail($player);
					$event->cancel();
				} else {
					$this->reward($player, 0.001);
				}
			}
		}
	}

	// advance position checking: we make sure the player blocks surroundings are calculated.
	public function flightV2(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();
		$oldPos = $event->getFrom();
		$newPos = $event->getTo();
		$surroundingBlocks = $this->GetSurroundingBlocks($player);
		if ($this->canBypass($player)) {
			return;
		}
		if (!$player->isCreative() && !$player->isSpectator() && !$player->getAllowFlight()) {
			if ($oldPos->getY() <= $newPos->getY()) {
				if ($player->getInAirTicks() > 40) {
					$maxY = $player->getWorld()->getHighestBlockAt(intval($newPos->getX()), intval($newPos->getZ()));
					if ($newPos->getY() - 2 > $maxY) {
						if (
							!in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::COBBLESTONE_WALL, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::ACACIA_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::BIRCH_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::DARK_OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::JUNGLE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::NETHER_BRICK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::SPRUCE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::WARPED_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::MANGROVE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CRIMSON_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CHERRY_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::ACACIA_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::OAK_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::BIRCH_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::DARK_OAK_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::JUNGLE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::SPRUCE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::WARPED_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::MANGROVE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CRIMSON_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CHERRY_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::HARDENED_GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::STAINED_GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::STAINED_HARDENED_GLASS_PANE, $surroundingBlocks, true)
						) {
							$event->cancel();
							$this->fail($player);
						} else {
							$this->reward($player, 0.01);
						}
					}
				}
			}
		}
	}

	public function GetSurroundingBlocks(Player $player) : array {
		$world = $player->getWorld();

		$posX = $player->getLocation()->getX();
		$posY = $player->getLocation()->getY();
		$posZ = $player->getLocation()->getZ();

		$pos1 = new Vector3($posX  , $posY, $posZ  );
		$pos2 = new Vector3($posX - 1, $posY, $posZ  );
		$pos3 = new Vector3($posX - 1, $posY, $posZ - 1);
		$pos4 = new Vector3($posX  , $posY, $posZ - 1);
		$pos5 = new Vector3($posX + 1, $posY, $posZ  );
		$pos6 = new Vector3($posX + 1, $posY, $posZ + 1);
		$pos7 = new Vector3($posX  , $posY, $posZ + 1);
		$pos8 = new Vector3($posX + 1, $posY, $posZ - 1);
		$pos9 = new Vector3($posX - 1, $posY, $posZ + 1);

		$bpos1 = $world->getBlock($pos1)->getTypeId();
		$bpos2 = $world->getBlock($pos2)->getTypeId();
		$bpos3 = $world->getBlock($pos3)->getTypeId();
		$bpos4 = $world->getBlock($pos4)->getTypeId();
		$bpos5 = $world->getBlock($pos5)->getTypeId();
		$bpos6 = $world->getBlock($pos6)->getTypeId();
		$bpos7 = $world->getBlock($pos7)->getTypeId();
		$bpos8 = $world->getBlock($pos8)->getTypeId();
		$bpos9 = $world->getBlock($pos9)->getTypeId();

		return  [$bpos1, $bpos2, $bpos3, $bpos4, $bpos5, $bpos6, $bpos7, $bpos8, $bpos9];
	}
}
