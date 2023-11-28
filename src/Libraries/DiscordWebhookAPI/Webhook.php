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

use Zuri\Libraries\libasynCurl\Curl;
use function filter_var;
use function json_encode;

class Webhook {
	/** @var string */
	protected $url;

	public function __construct(string $url) {
		$this->url = $url;
	}

	public function getURL() : string {
		return $this->url;
	}

	public function isValid() : bool {
		return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
	}

	public function send(Message $message) : void {
		Curl::postRequest($this->getURL(), json_encode($message), 10, ["Content-Type: application/json"]);
	}
}
