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

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\GameMode;
use Zuri\Zuri;
use function ceil;
use function floor;
use function microtime;

class Instabreak extends Zuri implements Listener {
	/** @var float[] */
	private $breakTimes = [];

	public function __construct() {
		parent::__construct(Zuri::INSTABREAK);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void {
		if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
			$this->breakTimes[$event->getPlayer()->getUniqueId()->getBytes()] = floor(microtime(true) * 20);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		if (!$event->getInstaBreak()) {
			$player = $event->getPlayer();

			if ($player->getGamemode()->equals(GameMode::SPECTATOR())) {
				return;
			}
			if ($this->canBypass($player)) {
				return;
			}

			if (!isset($this->breakTimes[$uuid = $player->getUniqueId()->getBytes()])) {
				$this->fail($player);
				$event->cancel();
				return;
			} else {
				$this->reward($player, 0.01);
			}

			$target = $event->getBlock();
			$item = $event->getItem();

			$expectedTime = ceil($target->getBreakInfo()->getBreakTime($item) * 20);

			if (($haste = $player->getEffects()->get(VanillaEffects::HASTE())) !== null) {
				$expectedTime *= 1 - (0.2 * $haste->getEffectLevel());
			}

			if (($miningFatigue = $player->getEffects()->get(VanillaEffects::MINING_FATIGUE())) !== null) {
				$expectedTime *= 1 + (0.3 * $miningFatigue->getEffectLevel());
			}

			$expectedTime -= 1; //1 tick compensation

			$actualTime = ceil(microtime(true) * 20) - $this->breakTimes[$uuid];

			if ($actualTime < $expectedTime) {
				$this->fail($player);
				$event->cancel();
				return;
			} else {
				$this->reward($player);
			}

			unset($this->breakTimes[$uuid]);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		unset($this->breakTimes[$event->getPlayer()->getUniqueId()->getBytes()]);
	}
}
