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
use pocketmine\player\GameMode;
use Zuri\Zuri;

class NoClip extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::NOCLIP);
	}

	public function onMove(PlayerMoveEvent $event) {
		if ($this->canBypass($event->getPlayer())) {
			return;
		}
		if ($this->isLagging($event->getPlayer())) {
			return;
		}
		$id = $event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, -1, 0))->getTypeId();
		if ($event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, 1, 0))->isSolid() && $event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, -1, 0))->isSolid() && $event->getPlayer()->getGamemode() !== GameMode::SPECTATOR()) {
			switch($id) {
				// Anti-false positive on falling blocks
				case BlockTypeIds::SAND:
				case BlockTypeIds::GRAVEL:
					// Prevent false positive on fence & fence gates
				case BlockTypeIds::ACACIA_FENCE:
				case BlockTypeIds::OAK_FENCE:
				case BlockTypeIds::BIRCH_FENCE:
				case BlockTypeIds::DARK_OAK_FENCE:
				case BlockTypeIds::JUNGLE_FENCE:
				case BlockTypeIds::NETHER_BRICK_FENCE:
				case BlockTypeIds::SPRUCE_FENCE:
				case BlockTypeIds::WARPED_FENCE:
				case BlockTypeIds::MANGROVE_FENCE:
				case BlockTypeIds::CRIMSON_FENCE:
				case BlockTypeIds::CHERRY_FENCE:
				case BlockTypeIds::ACACIA_FENCE_GATE:
				case BlockTypeIds::OAK_FENCE_GATE:
				case BlockTypeIds::BIRCH_FENCE_GATE:
				case BlockTypeIds::DARK_OAK_FENCE_GATE:
				case BlockTypeIds::JUNGLE_FENCE_GATE:
				case BlockTypeIds::SPRUCE_FENCE_GATE:
				case BlockTypeIds::WARPED_FENCE_GATE:
				case BlockTypeIds::MANGROVE_FENCE_GATE:
				case BlockTypeIds::CRIMSON_FENCE_GATE:
				case BlockTypeIds::CHERRY_FENCE_GATE:
					// prevent glitching on cobblestone walls
				case BlockTypeIds::COBBLESTONE_WALL:
					// Prevent false positive on glass panes and building blocks.
				case BlockTypeIds::GLASS_PANE:
				case BlockTypeIds::HARDENED_GLASS_PANE:
				case BlockTypeIds::STAINED_GLASS_PANE:
				case BlockTypeIds::STAINED_HARDENED_GLASS_PANE:
					// Prevent false positive on trapdoors
				case BlockTypeIds::COBWEB:
				case BlockTypeIds::ACACIA_TRAPDOOR:
				case BlockTypeIds::OAK_TRAPDOOR:
				case BlockTypeIds::BIRCH_TRAPDOOR:
				case BlockTypeIds::DARK_OAK_TRAPDOOR:
				case BlockTypeIds::JUNGLE_TRAPDOOR:
				case BlockTypeIds::SPRUCE_TRAPDOOR:
				case BlockTypeIds::WARPED_TRAPDOOR:
				case BlockTypeIds::MANGROVE_TRAPDOOR:
				case BlockTypeIds::CRIMSON_TRAPDOOR:
				case BlockTypeIds::CHERRY_TRAPDOOR:
				case BlockTypeIds::CARPET:
				case BlockTypeIds::CACTUS:
				case BlockTypeIds::BELL:
				case BlockTypeIds::BED:
					$this->reward($player);
					break;
				default:
					$event->cancel();
					$this->fail($event->getPlayer());
					break;
			}
			return;
		}
	}
}
