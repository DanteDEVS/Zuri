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
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\GameMode;
use Zuri\Anticheat;
use Zuri\Zuri;

class Speed extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::SPEED);
	}

	public function speedV1(PlayerMoveEvent $event) : void {
		if ($event->getPlayer()->getEffects()->has(VanillaEffects::SPEED())) {
			return;
		}

		if ($event->getPlayer()->isFlying()) {
			return;
		}
		$player = $event->getPlayer();
		if ($this->canBypass($player)) {
			return;
		}

		if ($this->isLagging($player)) {
			return;
		}
		if (($event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, 2, 0))->isSolid() && $event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getLocation()->add(0, -1, 0))->isSolid()) && $event->getPlayer()->getGamemode() !== GameMode::SPECTATOR()) {
			return;
		}
		if (($d = Zuri::XZDistanceSquared($event->getFrom(), $event->getTo())) > Anticheat::getInstance()->getConfig()->get("max-speed", 0.9)) {
			$this->fail($event->getPlayer());
		} elseif ($d > 1.1) {
			$event->cancel();
			$this->fail($event->getPlayer());
		} else {
			$this->reward($event->getPlayer(), 0.01);
		}
	}
}
