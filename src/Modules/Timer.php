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

class Timer extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::TIMER);
	}

	private array $time = [];
	private array $timer = [];
	private array $ticks = [];

	public function handleEvent(DataPacketReceiveEvent $event) : void {
		$this->handlePacket($event->getPacket(), $event->getOrigin());
	}

	public function handlePacket(DataPacket|Packet $packet, NetworkSession $session) : void {
		assert($packet instanceof PlayerAuthInputPacket);
		if (($player = $session->getPlayer()) === null) {
			return;
		}

		if (!isset($this->time[$player->getUniqueId()->__toString()])) {
			$this->time[$player->getUniqueId()->__toString()] = 0;
		}
		if (!isset($this->ticks[$player->getUniqueId()->__toString()])) {
			$this->ticks[$player->getUniqueId()->__toString()] = 0;
		}
		if (!isset($this->timer[$player->getUniqueId()->__toString()])) {
			$this->timer[$player->getUniqueId()->__toString()] = 0;
		}

		$time = $this->time[$player->getUniqueId()->__toString()];
		$ticks = $this->ticks[$player->getUniqueId()->__toString()];
		$timer = $this->timer[$player->getUniqueId()->__toString()];

		if (microtime(true) - $time > 1) {
			if ($ticks > 20) {
				$timer++;
				if ($timer % 10 === 0) {
					$this->fail($player);
				} else {
					$this->reward($player);
				}
			} else {
				$timer = 0;
			}

			$ticks = 0;
			$time = microtime(true);
		}

		$timer = 0;
		$time = microtime(true);
		$ticks = ++$ticks;
	}
}
