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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as TF;
use function count;

class ZuriCommand extends Command implements PluginOwned {
	public function __construct() {
		parent::__construct("zuri", "Anticheat Command", "/zuri <on/off|list>", ["anticheat"]);
		$this->setPermission(Anticheat::getInstance()->getConfig()->get("bypass-permission", "zuri.bypass"));
	}

	public function getOwningPlugin() : Anticheat {
		return Anticheat::getInstance();
	}

	public function execute(CommandSender $sender, string $label, array $args) : void {
		if ((count($args) === 0) && empty($args[0])) {
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . "Usage: " . $this->usageMessage);
		} elseif (isset($args[0]) && ($args[0] === "on" || $args[0] === "enable") && Zuri::$enabled === false) {
			Zuri::load();
			Zuri::$enabled = true;
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::GREEN . "The anticheat is now enabled.");
		} elseif (isset($args[0]) && ($args[0] === "on" || $args[0] === "enable") && Zuri::$enabled === true) {
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . "The anticheat is already enabled.");
		} elseif (isset($args[0]) && ($args[0] === "off" || $args[0] === "disable") && Zuri::$enabled === true) {
			Zuri::unload();
			Zuri::$enabled = false;
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::DARK_RED . "The anticheat is now disabled.");
		} elseif (isset($args[0]) && ($args[0] === "modules" || $args[0] === "list")) {
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::AQUA . "These are enabled modules in the anticheat:");
			foreach (Zuri::$enabledModules as $module) {
				$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::GREEN . "- " . $module->typeIdToString($module->getFlagId()));
			}
		} else {
			$sender->sendMessage(Zuri::PREFIX . " " . Zuri::ARROW . " " . TF::RED . "Usage: " . $this->usageMessage);
		}
	}
}
