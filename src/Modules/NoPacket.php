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
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use Zuri\Zuri;
use function assert;
use function microtime;

class NoPacket extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::NOPACKET);
	}

	private array $elapse = [];

	public function handleEvent(DataPacketReceiveEvent $event) : void {
		if ($this->handlePacket($event->getPacket(), $event->getOrigin())) {
			$event->cancel();
		}
	}

	public function handlePacket(Packet|DataPacket $packet, NetworkSession $session) : bool {
		assert($packet instanceof PlayerAuthInputPacket);
		if (($player = $session->getPlayer()) === null) {
			return false;
		}
		if ($this->canBypass($player)) {
			return false;
		}
		if($this->isLagging($player)) return false;

		$this->elapse[$player->getUniqueId()->__toString()] = microtime(true);
		$last = $this->elapse[$player->getUniqueId()->__toString()];
		$elapse = microtime(true) - $last;

		if ($elapse > 3) {
			$this->fail($player);
			return true;
		} else {
			$this->reward($player);
		}

		return false;
	}
}
