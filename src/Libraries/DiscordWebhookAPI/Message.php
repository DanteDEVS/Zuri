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
use function array_key_exists;

class Message implements JsonSerializable {
	/** @var array */
	protected $data = [];

	public function setContent(string $content) : void {
		$this->data["content"] = $content;
	}

	public function getContent() : ?string {
		return $this->data["content"];
	}

	public function getUsername() : ?string {
		return $this->data["username"];
	}

	public function setUsername(string $username) : void {
		$this->data["username"] = $username;
	}

	public function getAvatarURL() : ?string {
		return $this->data["avatar_url"];
	}

	public function setAvatarURL(string $avatarURL) : void {
		$this->data["avatar_url"] = $avatarURL;
	}

	public function addEmbed(Embed $embed) : void {
		if (!empty(($arr = $embed->asArray()))) {
			$this->data["embeds"][] = $arr;
		}
	}

	public function addComponent(Component $component) : void {
		if (!empty(($arr = $component->asArray()))) {
			$this->data["components"][] = $arr;
		}
	}

	public function setTextToSpeech(bool $ttsEnabled) : void {
		$this->data["tts"] = $ttsEnabled;
	}

	public function getAllowedMentions() : AllowedMentions {
		if (array_key_exists("allowed_mentions", $this->data)) {
			return $this->data["allowed_mentions"];
		}

		return $this->data["allowed_mentions"] = new AllowedMentions();
	}

	public function jsonSerialize() : mixed {
		return $this->data;
	}
}
