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
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use Zuri\Anticheat;
use Zuri\Zuri;

class HighJump extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::HIGHJUMP);
	}

	private array $lastFrom = [];
	private array $lastTo = [];

	public function highJumpV1(PlayerJumpEvent $event) : void {
		$player = $event->getPlayer();

		if ($this->canBypass($player)) {
			return;
		}
		if ($this->isLagging($player)) {
			return;
		}
		$f = $this->lastFrom[$player->getUniqueId()->getBytes()];
		$t = $this->lastTo[$player->getUniqueId()->getBytes()];
		$d = $f->distance($t);
		if ($t > $f) {
			if ($d >= Anticheat::getInstance()->getConfig()->get("max-jump-blocks", 3)) {
				if (!$player->getEffects()->has(VanillaEffects::JUMP_BOOST())) {
					$this->fail($player);
				}
			}
		}
	}

	public function onMove(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();
		$this->lastFrom[$player->getUniqueId()->getBytes()] = $event->getFrom();
		$this->lastTo[$player->getUniqueId()->getBytes()] = $event->getTo();
	}
}
