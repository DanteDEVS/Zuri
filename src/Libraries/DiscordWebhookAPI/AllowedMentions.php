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

declare(strict_types = 1);

namespace Zuri\Libraries\DiscordWebhookAPI;

use JsonSerializable;
use function count;
use function in_array;

class AllowedMentions implements JsonSerializable {
	/** @var bool */
	private $parseUsers = true, $parseRoles = true, $mentionEveryone = true, $suppressAll = false;

	/** @var array */
	private $roles = [];

	/** @var array */
	private $users = [];

	private $data = [];

	/**
	 * If following role is given into the messages content, every user of it will be mentioned
	 */
	public function addRole(string ...$roleID) : void {
		foreach ($roleID as $item) {
			if (in_array($item, $this->roles, true)) {
				continue;
			}

			$this->roles[] = $item;
		}
		$this->parseRoles = false;
	}

	/**
	 * If following user is given into the messages content, the user will be mentioned
	 */
	public function addUser(string ...$userID) : void {
		foreach ($userID as $item) {
			if (in_array($item, $this->users, true)) {
				continue;
			}

			$this->users[] = $item;
		}

		$this->parseUsers = false;
	}

	/**
	 * If the message content has whether everyone or here and $mention is set to false, the users won't be mentioned
	 */
	public function mentionEveryone(bool $mention) : void {
		$this->mentionEveryone = $mention;
	}

	/**
	 * If this function is called no mention will be getting showed for anyone
	 */
	public function suppressAll() : void {
		$this->suppressAll = true;
	}

	public function jsonSerialize() : mixed {
		if ($this->suppressAll) {
			return [
				"parse" => []
			];
		}

		$data = ["parse" => []];
		if ($this->mentionEveryone) {
			$data["parse"][] = "everyone";
		}

		if (count($this->users) !== 0) {
			$data["users"] = $this->users;
		} elseif ($this->parseUsers) {
			$data["parse"][] = "users";
		}

		if (count($this->roles) !== 0) {
			$data["roles"] = $this->roles;
		} elseif ($this->parseRoles) {
			$data["parse"][] = "roles";
		}

		return $data;
	}

	public function asArray() : array {
		// Why doesn't PHP have a `__toArray()` magic method??? This would've been better.
		return $this->data;
	}
}
