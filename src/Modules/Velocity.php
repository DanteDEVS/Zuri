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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Zuri\Anticheat;
use Zuri\Zuri;
use function pow;
use function spl_object_id;
use function sqrt;

class Velocity extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::VELOCITY);
	}

	protected array $lastLocMonitor = [];

	public function VelocityV1(EntityDamageEvent $ev) : void {
		$player = $ev->getEntity();
		if ($player instanceof Player) {
			if ($this->canBypass($player)) {
				return;
			}
			$loc = $player->getLocation();
			if (isset($this->lastLocMonitor[spl_object_id($player)]) && ($lastLoc = $this->lastLocMonitor[spl_object_id($player)]) !== null) {
				if (!$ev->isCancelled() || $player->isOnGround()) {
					$velocity = Velocity::distance($loc->asVector3(), $lastLoc->asVector3());

					if ($velocity < Anticheat::getInstance()->getConfig()->get("velocity", 0.6) && !$this->isLagging($player)) {
						$this->fail($player);
					} else {
						$this->reward($player, 0.01);
					}
				}
				unset($this->lastLocMonitor[spl_object_id($player)]);
			} else {
				$this->lastLocMonitor[spl_object_id($player)] = $loc;
			}
		}
	}

	public static function distance(Vector3 $from, Vector3 $to) {
		return sqrt(pow($from->getX() - $to->getX(), 2) + pow($from->getY() - $to->getY(), 2) + pow($from->getZ() - $to->getZ(), 2));
	}
}
