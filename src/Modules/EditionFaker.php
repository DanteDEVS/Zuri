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
use pocketmine\utils\TextFormat as TF;
use Zuri\Anticheat;
use Zuri\Zuri;
use function explode;
use function in_array;
use function strtoupper;

class EditionFaker extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::EDITION_FAKER);
	}

	public const IP_LIMIT = 3;

	public const NULL_MODELS = [
		DeviceOS::ANDROID,
		DeviceOS::OSX,
		DeviceOS::WINDOWS_10,
		DeviceOS::WIN32,
		DeviceOS::DEDICATED,
	];

	public const DEVICE_OS_LIST = [
		DeviceOS::ANDROID,
		DeviceOS::IOS,
		DeviceOS::AMAZON,
		DeviceOS::WINDOWS_10,
		DeviceOS::WIN32,
		DeviceOS::PLAYSTATION,
		DeviceOS::NINTENDO,
		DeviceOS::XBOX
	];

	public function checkOS(PlayerPreLoginEvent $event) : void {
		$playerInfo = $event->getPlayerInfo();
		$extraData = $playerInfo->getExtraData();
		$nickname = $playerInfo->getUsername();

		if (($player = Anticheat::getInstance()->getServer()->getPlayerExact($event->getPlayerInfo()->getUsername())) !== null) {
			if ($this->canBypass($player)) {
				return;
			}
		}

		/** @var Player[] $playersToKick */
		$playersToKick = [];
		$count = 0;
		foreach (Anticheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
			if (!($player->isConnected())) {
				continue;
			}

			if ($event->getIp() === $player->getNetworkSession()->getIp()) {
				$playersToKick[] = $player;
				$count++;
			}
		}

		if ($count >= EditionFaker::IP_LIMIT) {
			foreach ($playersToKick as $player) {
				if ($player->isConnected()) {
					$this->fail($player);
					$this->kick($player, $this->typeToReasonString($this->getFlagId()));
				}
			}
			$this->notifyAdmins($nickname, true);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if (!(in_array($extraData["DeviceOS"], EditionFaker::DEVICE_OS_LIST, true))) {
			$this->notifyAdmins($nickname, true);
			$this->fail($player);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if (!(in_array($extraData["DeviceOS"], EditionFaker::NULL_MODELS, true)) && $extraData["DeviceModel"] === "") {
			$this->notifyAdmins($nickname, true);
			$this->fail($player);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if ($extraData["DeviceOS"] === DeviceOS::ANDROID) {
			$model = explode(" ", $extraData["DeviceModel"], 2)[0];
			if ($model !== strtoupper($model) && $model !== "") {
				$this->notifyAdmins($nickname, true);
				$this->fail($player);
				$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
				return;
			}
		}

		if ($extraData["DeviceOS"] === DeviceOS::IOS) {
			if ($extraData["DeviceId"] !== strtoupper($extraData["DeviceId"])) {
				$this->notifyAdmins($nickname, true);
				$this->fail($player);
				$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			}
		}
	}
}
