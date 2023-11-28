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

/**
 * TODO:
 * - Add Buttons instead only for links.
 * - Discord Button Interact -> Plugin Action if needed.
 * - Button Colors for not discord button link.
 */
class Component {
	/** @var array **/
	protected $data = [];

	public function asArray() : array {
		// Why doesn't PHP have a `__toArray()` magic method??? This would've been better.
		return $this->data;
	}

	/**
	 * Add Link Button from the message.
	 */
	public function addLinkButton(string $text, string $link) {
		if (!isset($this->data["type"])) {
			$this->data["type"] = 1; // container for other components
		}
		if (!isset($this->data["components"])) {
			$this->data["components"] = [];
		}

		$this->data["components"]["type"] = 5;
		$this->data["components"]["label"] = $text;
		$this->data["components"]["url"] = $link;
	}
}
