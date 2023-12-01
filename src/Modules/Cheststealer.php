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

use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\PlayerCraftingInventory;
use pocketmine\inventory\PlayerInventory;
use Zuri\Zuri;
use function microtime;

class Cheststealer extends Zuri implements Listener {
	public function __construct() {
		parent::__construct(Zuri::CHESTSTEALER);
	}

	private array $time = [];
	private array $count = [];

	public function OpenInventory(InventoryOpenEvent $event) : void {
		$player = $event->getPlayer();
		if ($this->canBypass($player)) {
			return;
		}
		if (!isset($this->count[$player->getUniqueId()->getBytes()])) {
			$this->count[$player->getUniqueId()->getBytes()] = null;
		}

		if (!isset($this->time[$player->getUniqueId()->getBytes()])) {
			$this->time[$player->getUniqueId()->getBytes()] = null;
		}
		$countTransaction = $this->count[$player->getUniqueId()->getBytes()];
		$timeOpenChest = $this->time[$player->getUniqueId()->getBytes()];
		if ($timeOpenChest === null && !($event->getInventory() instanceof PlayerCraftingInventory)) {
			$this->time[$player->getUniqueId()->getBytes()] = microtime(true);
		}
	}

	public function CloseInventory(InventoryCloseEvent $event) : void {
		$player = $event->getPlayer();
		if ($this->canBypass($player)) {
			return;
		}

		if (!isset($this->count[$player->getUniqueId()->getBytes()])) {
			$this->count[$player->getUniqueId()->getBytes()] = null;
		}

		if (!isset($this->time[$player->getUniqueId()->getBytes()])) {
			$this->time[$player->getUniqueId()->getBytes()] = null;
		}

		$countTransaction = $this->count[$player->getUniqueId()->getBytes()];
		$timeOpenChest = $this->time[$player->getUniqueId()->getBytes()];
		if ($timeOpenChest !== null && $countTransaction !== null) {
			$timeDiff = microtime(true) - $timeOpenChest;
			if ($timeDiff < $countTransaction / 5) {
				$this->fail($player, 5);
			} else {
				$this->reward($player, 1);
			}
			unset($this->count[$player->getUniqueId()->getBytes()]);
			unset($this->time[$player->getUniqueId()->getBytes()]);
			//debug purpose:
			//$player->sendMessage("count: " . $countTransaction . ", time: " . $timeOpenChest . ", timeDiff: " . $timeDiff);
		}
	}

	public function InventoryTransaction(InventoryTransactionEvent $event) : void {
		$transaction = $event->getTransaction();
		$player = $transaction->getSource();
		if ($this->canBypass($player)) {
			return;
		}
		if (!isset($this->count[$player->getUniqueId()->getBytes()])) {
			$this->count[$player->getUniqueId()->getBytes()] = null;
		}

		if (!isset($this->time[$player->getUniqueId()->getBytes()])) {
			$this->time[$player->getUniqueId()->getBytes()] = null;
		}

		$countTransaction = $this->count[$player->getUniqueId()->getBytes()];
		$timeOpenChest = $this->time[$player->getUniqueId()->getBytes()];

		foreach ($transaction->getInventories() as $inventory) {
			if ($inventory instanceof PlayerInventory) {
				if ($countTransaction !== null && $timeOpenChest !== null) {
					$this->count[$player->getUniqueId()->getBytes()] = $countTransaction + 1;
				} else {
					$this->count[$player->getUniqueId()->getBytes()] = 0;
				}
			}
		}
		//debug purpose:
		//$player->sendMessage("count: " . $countTransaction . " time: " . $timeOpenChest);
	}
}
