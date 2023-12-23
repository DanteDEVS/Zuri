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
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use Zuri\Anticheat;
use Zuri\Zuri;
use function explode;
use function strtoupper;

class Antibot extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::ANTIBOT);
	}

	public function handleEvent(PlayerPreLoginEvent $event) : void {
		$extraData = $event->getPlayerInfo()->getExtraData();
		if (($player = Anticheat::getInstance()->getServer()->getPlayerExact($event->getPlayerInfo()->getUsername())) !== null) {
			if ($this->canBypass($player)) {
				return;
			}
			if($this->isLagging($player)) return;
		}
		if ($extraData["DeviceOS"] === DeviceOS::ANDROID) {
			$model = explode(" ", $extraData["DeviceModel"], 2)[0];
			if ($model !== strtoupper($model) && $model !== "") {
				Anticheat::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event) {
					if (($player = Anticheat::getInstance()->getServer()->getPlayerExact($event->getPlayerInfo()->getUsername())) !== null) {
						$this->fail($player);
						$this->kick($player, $this->typeToReasonString($this->getFlagId()));
					}
				}), 2);
			}
		}
	}
}
