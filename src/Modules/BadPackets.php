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
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\player\Player;
use Zuri\Zuri;
use function abs;
use function mb_strlen;

class BadPackets extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::BADPACKET);
	}

	public array $packetsPerSecond = [];

	public const MESSAGE_LIMIT = 500;

	// some people bypass message limit, so to prevent message vulnerabilities, we check this.
	public function badPacketV2(DataPacketReceiveEvent $event) : void {
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if (!($player instanceof Player)) {
			return;
		}
		if ($this->canBypass($player)) {
			return;
		}
		if($this->isLagging($player)) return;
		if ($packet instanceof TextPacket) {
			if (mb_strlen($packet->message) > BadPackets::MESSAGE_LIMIT) {
				if ($player->getRankId() === Rank::OWNER) {
					return;
				}
				$event->cancel();
				$this->fail($player);
			} else {
				$this->reward($player);
			}
		}
	}

	public function badPacketV1(DataPacketReceiveEvent $event) : void {
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if (!($player instanceof Player)) {
			return;
		}
		if($this->isLagging($player)) return;

		if ($packet instanceof PlayerAuthInputPacket && abs($packet->getPitch()) > 92) {
			$this->fail($player);
		}
	}
}
