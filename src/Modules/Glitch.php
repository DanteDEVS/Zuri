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

use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Trapdoor;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Zuri\Anticheat;
use Zuri\MotionTask;
use Zuri\TeleportTask;
use Zuri\Zuri;
use function abs;
use function max;
use function range;

class Glitch extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::GLITCH);
	}

	private array $pearlland = [];

	public function onPearlLandBlock(ProjectileHitEvent $event) {
		$player = $event->getEntity()->getOwningEntity();
		if ($player instanceof Player && $event->getEntity() instanceof EnderPearl) {
			$this->pearlland[$player->getName()] = Core::getInstance()->getServer()->getTick();
		}
	}

	public function onTP(EntityTeleportEvent $event) {
		$entity = $event->getEntity();
		if (!$entity instanceof Player) {
			return;
		}
		$world = $entity->getWorld();
		$to = $event->getTo();
		if (!isset($this->pearlland[$entity->getName()])) {
			return;
		}
		if ($this->canBypass($entity)) {
			return;
		}
		if($this->isLagging($player)) return;
		if (Core::getInstance()->getServer()->getTick() != $this->pearlland[$entity->getName()]) {
			return;
		} //Check if teleportation was caused by enderpearl (by checking is a projectile landed at the same time as teleportation) TODO Find a less hacky way of doing this?

		//Get coords and adjust for negative quadrants.
		$x = $to->getX();
		$y = $to->getY();
		$z = $to->getZ();
		if ($x < 0) {
			$x = $x - 1;
		}
		if ($z < 0) {
			$z = $z - 1;
		}

		//If pearl is in a block as soon as it lands (which could only mean it was shot into a block over a fence), put it back down in the fence. TODO Find a less hacky way of doing this?
		if ($this->isInHitbox($world, $x, $y, $z)) {
			$y = $y - 0.5;
		}

		if ($this->isInHitbox($world, $entity->getLocation()->getX(), $entity->getLocation()->getY() + 1.5, $entity->getLocation()->getZ())) {
			$this->fail($entity);
			$event->cancel();
			return;
		}

		//Try to find a good place to teleport.
		$ys = $y;
		foreach (range(0, 1.9, 0.05) as $n) {
			$xb = $x;
			$yb = ($ys - $n);
			$zb = $z;

			if ($this->isInHitbox($world, ($x + 0.05), $yb, $z)) {
				$xb = $xb - 0.3;
			}
			if ($this->isInHitbox($world, ($x - 0.05), $yb, $z)) {
				$xb = $xb + 0.3;
			}
			if ($this->isInHitbox($world, $x, $yb, ($z - 0.05))) {
				$zb = $zb + 0.3;
			}
			if ($this->isInHitbox($world, $x, $yb, ($z + 0.05))) {
				$zb = $zb - 0.3;
			}

			if ($this->isInHitbox($world, $xb, $yb, $zb)) {
				break;
			} else {
				$x = $xb;
				$y = $yb;
				$z = $zb;
			}
		}

		//Check if pearl lands in an area too small for the player
		foreach (range(0.1, 1.8, 0.1) as $n) {
			if ($this->isInHitbox($world, $x, ($y + $n), $z)) {
				//Teleport the player into the middle of the block so they can't phase into an adjacent block.
				if (isset($world->getBlockAt((int) $xb, (int) $yb, (int) $zb)->getCollisionBoxes()[0])) {
					$blockHitBox = $world->getBlockAt((int) $xb, (int) $yb, (int) $zb)->getCollisionBoxes()[0];
					if ($x < 0) {
						$x = (($blockHitBox->minX + $blockHitBox->maxX) / 2) - 1;
					} else {
						$x = ($blockHitBox->minX + $blockHitBox->maxX) / 2;
					}
					if ($z < 0) {
						$z = (($blockHitBox->minZ + $blockHitBox->maxZ) / 2) - 1;
					} else {
						$z = ($blockHitBox->minZ + $blockHitBox->maxZ) / 2;
					}
				}
				//Prevent pearling into areas too small
				$this->fail($entity);
				$event->cancel();
				if ($x < 0) {
					$x = $x + 1;
				}
				if ($z < 0) {
					$z = $z + 1;
				}
				Anticheat::getInstance()->getScheduler()->scheduleDelayedTask(new TeleportTask($entity, new Location($x, $y, $z, $entity->getWorld(), $entity->getLocation()->getYaw(), $entity->getLocation()->getPitch())), 5);
			} else {
				$this->reward($player, 0.01);
			}
		}

		//Readjust for negative quadrants
		if ($x < 0) {
			$x = $x + 1;
		}
		if ($z < 0) {
			$z = $z + 1;
		}

		//Send new safe location
		$event->setTo(new Position($x, $y, $z, $world));
	}

	public function isInHitbox($level, $x, $y, $z) {
		if (!isset($level->getBlockAt((int) $x, (int) $y, (int) $z)->getCollisionBoxes()[0])) {
			return False;
		}
		foreach ($level->getBlockAt((int) $x, (int) $y, (int) $z)->getCollisionBoxes() as $blockHitBox) {
			if ($x < 0) {
				$x = $x + 1;
			}
			if ($z < 0) {
				$z = $z + 1;
			}
			if (($blockHitBox->minX < $x) && ($x < $blockHitBox->maxX) && ($blockHitBox->minY < $y) && ($y < $blockHitBox->maxY) && ($blockHitBox->minZ < $z) && ($z < $blockHitBox->maxZ)) {
				return True;
			}
		}
		return False;
	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer();
		$block = $event->getBlockAgainst();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player)) return;
		if ($player->isCreative() || $player->isSpectator()) {
			return;
		}
		if ($event->isCancelled()) {
			$playerX = $player->getLocation()->getX();
			$playerZ = $player->getLocation()->getZ();
			if ($playerX < 0) {
				$playerX = $playerX - 1;
			}
			if ($playerZ < 0) {
				$playerZ = $playerZ - 1;
			}
			if (($block->getPosition()->getX() == (int) $playerX) && ($block->getPosition()->getZ() == (int) $playerZ) && ($player->getPosition()->getY() > $block->getPosition()->getY())) { #If block is under the player
				$playerMotion = $player->getMotion();
				Anticheat::getInstance()->getScheduler()->scheduleDelayedTask(new MotionTask($player, new Vector3($playerMotion->getX(), -0.1, $playerMotion->getZ())), 2);
				$this->fail($player);
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player)) return;
		if ($player->isCreative() || $player->isSpectator()) {
			return;
		}
		if ($event->isCancelled()) {
			$x = $player->getLocation()->getX();
			$y = $player->getLocation()->getY();
			$z = $player->getLocation()->getZ();
			$playerX = $player->getLocation()->getX();
			$playerZ = $player->getLocation()->getZ();
			if ($playerX < 0) {
				$playerX = $playerX - 1;
			}
			if ($playerZ < 0) {
				$playerZ = $playerZ - 1;
			}
			if (($block->getPosition()->getX() == (int) $playerX) && ($block->getPosition()->getZ() == (int) $playerZ) && ($player->getLocation()->getY() > $block->getPosition()->getY())) { #If block is under the player
				foreach ($block->getCollisionBoxes() as $blockHitBox) {
					$y = max([$y, $blockHitBox->maxY]);
				}
				$player->teleport(new Vector3($x, $y, $z));
			} else { #If block is on the side of the player
				$xb = 0;
				$zb = 0;
				foreach ($block->getCollisionBoxes() as $blockHitBox) {
					if (abs($x - ($blockHitBox->minX + $blockHitBox->maxX) / 2) > abs($z - ($blockHitBox->minZ + $blockHitBox->maxZ) / 2)) {
						$xb = (5 / ($x - ($blockHitBox->minX + $blockHitBox->maxX) / 2)) / 24;
					} else {
						$zb = (5 / ($z - ($blockHitBox->minZ + $blockHitBox->maxZ) / 2)) / 24;
					}
				}
				$player->setMotion(new Vector3($xb, 0, $zb));
			}
			$this->fail($player);
		}
	}

	public function onInteract(PlayerInteractEvent $event) {
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
			return;
		}
		$player = $event->getPlayer();
		if ($player->isCreative() || $player->isSpectator()) {
			return;
		}
		$block = $event->getBlock();
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player)) return;
		if ($event->isCancelled()) {
			if ($block instanceof Door || $block instanceof FenceGate || $block instanceof Trapdoor) {
				$x = $player->getLocation()->getX();
				$y = $player->getLocation()->getY();
				$z = $player->getLocation()->getZ();
				$playerX = $player->getLocation()->getX();
				$playerZ = $player->getLocation()->getZ();
				if ($playerX < 0) {
					$playerX = $playerX - 1;
				}
				if ($playerZ < 0) {
					$playerZ = $playerZ - 1;
				}
				if (($block->getPosition()->getX() == (int) $playerX) && ($block->getPosition()->getZ() == (int) $playerZ) && ($player->getLocation()->getY() > $block->getPosition()->getY())) { #If block is under the player
					foreach ($block->getCollisionBoxes() as $blockHitBox) {
						$y = max([$y, $blockHitBox->maxY + 0.05]);
					}
					$player->teleport(new Vector3($x, $y, $z), $player->getLocation()->getYaw(), 35);
				} else {
					foreach ($block->getCollisionBoxes() as $blockHitBox) {
						if (abs($x - ($blockHitBox->minX + $blockHitBox->maxX) / 2) > abs($z - ($blockHitBox->minZ + $blockHitBox->maxZ) / 2)) {
							$xb = (3 / ($x - ($blockHitBox->minX + $blockHitBox->maxX) / 2)) / 25;
							$zb = 0;
						} else {
							$xb = 0;
							$zb = (3 / ($z - ($blockHitBox->minZ + $blockHitBox->maxZ) / 2)) / 25;
						}
						$player->teleport($player->getLocation()->asVector3(), $player->getLocation()->getYaw(), 85);
						$player->setMotion(new Vector3($xb, 0, $zb));
					}
				}

				$this->fail($player);
			} else {
				$this->reward($player, 0.01);
			}
		}
	}
}
