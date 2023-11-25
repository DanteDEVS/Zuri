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
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\TextFormat as TF;
use Zuri\Anticheat;
use Zuri\Libraries\libasynCurl\Curl;
use Zuri\Zuri;
use function in_array;
use function json_decode;
use function str_contains;

class Proxy extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::PROXY);
	}

	public function antiProxyV1(PlayerPreLoginEvent $event) : void {
		$this->checkVPN($event->getPlayerInfo()->getUsername(), $event->getIp());
	}

	public function checkVPN(string $username, string $address) : void {
		if (($player = Anticheat::getInstance()->getServer()->getPlayerExact($username)) !== null && $player->isOnline() && $player->spawned) {
			$this->canBypass($player);
		}
		if (($key = Anticheat::getInstance()->getConfig()->get("api-key", "")) === "") {
			$url = "https://vpnapi.io/api/$address";
		} else {
			$url = "https://vpnapi.io/api/$address&key=$key";
		}
		Curl::getRequest($url, 10, ["Content-Type: application/json"], function(?InternetRequestResult $result) use ($username, $address) : void {
			if ($result !== null) {
				if (($response = json_decode($result->getBody(), true)) !== null) {
					if (in_array($address, Anticheat::getInstance()->getBypassConfig()->get("bypass-ip"), true)) {
						return;
					}

					if (isset($response["message"]) && $response["message"] !== "") {
						if ($address === "127.0.0.1" || $address === "::1" || $address === "0.0.0.0" || $address === "localhost" || str_contains($address, "192.168")) {
							Anticheat::getInstance()->getServer()->getLogger()->info(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . "Unable to check ip: " . TF::AQUA . $address . TF::RED . " Error: " . TF::DARK_RED . $response["message"] . TF::RED . ", is using local ip?");
							return;
						} else {
							Anticheat::getInstance()->getServer()->getLogger()->info(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . "Unable to check ip: " . TF::AQUA . $address . TF::RED . " Error: " . TF::DARK_RED . $response["message"]);
						}
						$this->checkVPN($username, $address);
						return;
					}

					if (isset($response["security"]["vpn"]) && isset($response["security"]["proxy"]) && isset($response["security"]["tor"]) && isset($response["security"]["relay"])) {
						if ($response["security"]["vpn"] === true || $response["security"]["proxy"] === true || $response["security"]["tor"] === true || $response["security"]["relay"] === true) {
							if (($player = Anticheat::getInstance()->getServer()->getPlayerExact($username)) !== null && $player->isOnline() && $player->spawned) {
								Anticheat::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) {
									$this->fail($player);
									$this->kick($player, $this->typeToReasonString($this->getFlagId()));
								}), 2);
								return;
							}
						}
					}
				}
			}
			$this->checkVPN($username, $address); // continuous excecution
		});
	}
}
