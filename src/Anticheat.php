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

namespace Zuri;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission as PMPermission;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\NotCloneable;
use pocketmine\utils\NotSerializable;
use pocketmine\utils\SingletonTrait;
use Zuri\Libraries\libasynCurl\Curl;

class Anticheat extends PluginBase {
	use NotSerializable;
	use NotCloneable;
	use SingletonTrait;

	public array $perm = [];
	public ?Config $bypassConfig = null;
	public ?PermissionAttachment $attachment = null;

	public function onLoad() : void {
		self::setInstance($this);
		$this->saveResource("config.yml");
		$this->saveResource("bypass.yml");
	}

	public function onEnable() : void {
		Curl::register($this);
		Zuri::load();
		$this->register($this->getConfig()->get("bypass-Permission", "zuri.bypass"), Anticheat::OPERATOR);
		$this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new ZuriCommand());
	}

	public const USER = 0;
	public const OPERATOR = 1;
	public const CONSOLE = 3;
	public const NONE = -1;

	protected function register(string $permission, int $permAccess, array $childPermission = []) : void {
		$this->perm[] = $permission;
		$perm = new PMPermission($permission, "PrideMC Network Permission", $childPermission);
		$permManager = PermissionManager::getInstance();
		switch($permAccess) {
			case Anticheat::USER:
				$p = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER);
				$p->addChild($perm->getName(), true);
				break;
			case Anticheat::OPERATOR:
				$p = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
				$p->addChild($perm->getName(), true);
				break;
			case Anticheat::CONSOLE:
				$p = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_CONSOLE);
				$p->addChild($perm->getName(), true);
				break;
			case Anticheat::NONE:
				$p = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER);
				$p->addChild($perm->getName(), false);
				break;
		}
		$permManager->addPermission($perm);
	}

	public function addPlayerPermissions(Player $player, array $permissions) : void {
		if ($this->attachment === null) {
			$this->attachment = $player->addAttachment(Core::getInstance());
		}
		$this->attachment->setPermissions($permissions);
		$player->getNetworkSession()->syncAvailableCommands();
	}

	public function resetPlayerPermissions() : void {
		if ($this->attachment === null) {
			return;
		}
		$this->attachment->clearPermissions();
	}

	public function getAllPermissions() : array {
		return $this->perm;
	}

	public function getBypassConfig() : Config {
		if ($this->bypassConfig === null) {
			$this->bypassConfig = new Config($this->getDataFolder() . "bypass.yml", Config::YAML);
		}

		return $this->bypassConfig;
	}
}