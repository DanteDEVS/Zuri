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
use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use Zuri\Zuri;
use function abs;
use function pow;
use function sqrt;

class Scaffold extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::SCAFFOLD);
	}
	
	// HACK CHANGES!! TO BE ON TESTING!!
	// This is to be intended only in touch scenes, but few things are needs to be tested
	// since mojang added new controls in 1.19, and it seems to be hard enough to fight this checks
	// agains the false-positive of the new controls, which gives hackers to do way dirty as well. Since
	// this controls might change the player block place behaivor, it would probably to be hard coded as well.
	// This might be fixed in the future, but mostly now it would probably in testing process since false-positive
	// changes. I would probably suggesting if mojang sends a statistics of settings of a player such as POV, Touch events,
	// or Mobile Control scenes, that would be could better and simpliest to detect players using Scaffold.
	
	// Status: 0% (Mobile, IOS Scene)
	public function MobileScaffold(BlockPlaceEvent $event)  : void {
		$player = $this->getPlayer();
		$block = $event->getBlockAgainst();
		$posBlock = $block->getPosition();
		$itemHand = $player->getInventory()->getItemInHand();
		$posPlayer = $player->getLocation();
		// Always check if player is on Android/IOS.
		if ($player->getPlayerInfo()->getExtraData()["DeviceOS"] === DeviceOS::ANDROID || $player->getPlayerInfo()->getExtraData()["DeviceOS"] === DeviceOS::IOS) {
			
			// Checks that are really important
			if($this->canBypass($player)) return;
			
			if($this->isLagging($player)) return;
			
			// the checks will only run if its their surrounding are air
			$surroundingBlocks = $this->GetSurroundingBlocks($player);
			
			if (
				in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::COBBLESTONE_WALL, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::ACACIA_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::BIRCH_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::DARK_OAK_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::JUNGLE_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::NETHER_BRICK_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::SPRUCE_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::WARPED_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::MANGROVE_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::CRIMSON_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::CHERRY_FENCE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::ACACIA_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::OAK_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::BIRCH_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::DARK_OAK_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::JUNGLE_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::SPRUCE_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::WARPED_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::MANGROVE_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::CRIMSON_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::CHERRY_FENCE_GATE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::GLASS_PANE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::HARDENED_GLASS_PANE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::STAINED_GLASS_PANE, $surroundingBlocks, true)
				|| in_array(BlockTypeIds::STAINED_HARDENED_GLASS_PANE, $surroundingBlocks, true)
					) {
						return;
					}
			
			// IMPOSIBLE: Not bad.
			if ($itemHand->getBlock() === VanillaBlocks::AIR()) {
				$x = $posBlock->getX();
				$y = $posBlock->getY();
				$z = $posBlock->getZ();
				if ($x > 1.0 || $z > 1.0 || $y > 1.0) {
					$this->fail($player);
					return;
				}
			}
			
			// Nullable Items but placing blocks? Are you okay toolbox?
			if ($player->getInventory()->getItemInHand()->isNull()) {
				$this->fail($player);
				return;
			}
			
			// Impossible head pitch placing blocks
			// bruh mobile players can't even place blocks under 4 blocks while heads are down
			$distance = Scaffold::distance($posPlayer->asVector3(), $posBlock->asVector3());
			if ($distance < 4 && round($posPlayer->getPitch()) > 10) {
				$this->fail($player);
				return;
			}
			
			$this->reward($player, 0.01); // for false-positive changes.
		}
	}
	
	

	public static function distance(Vector3 $from, Vector3 $to) {
		return sqrt(pow($from->getX() - $to->getX(), 2) + pow($from->getY() - $to->getY(), 2) + pow($from->getZ() - $to->getZ(), 2));
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
