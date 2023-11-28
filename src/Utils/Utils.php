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

namespace Zuri\Utils;

use pocketmine\utils\TextFormat as TF;
use function array_keys;
use function array_values;
use function str_replace;
use function strtoupper;

class Utils {
	public static function colorFormat(string $text) : string {
		$list = [
			"&" => TF::ESCAPE,
			"{BLACK}" => TF::BLACK,
			"{DARK_BLUE}" => TF::DARK_BLUE,
			"{DARK_GREEN}" => TF::DARK_GREEN,
			"{DARK_AQUA}" => TF::DARK_AQUA,
			"{DARK_RED}" => TF::DARK_RED,
			"{DARK_PURPLE}" => TF::DARK_PURPLE,
			"{GOLD}" => TF::GOLD,
			"{GRAY}" => TF::GRAY,
			"{DARK_GRAY}" => TF::DARK_GRAY,
			"{BLUE}" => TF::BLUE,
			"{GREEN}" => TF::GREEN,
			"{AQUA}" => TF::AQUA,
			"{RED}" => TF::RED,
			"{LIGHT_PURPLE}" => TF::LIGHT_PURPLE,
			"{YELLOW}" => TF::YELLOW,
			"{WHITE}" => TF::WHITE,
			"{MINECOIN_GOLD}" => TF::MINECOIN_GOLD,
			"{OBFUSCATED}" => TF::OBFUSCATED,
			"{BOLD}" => TF::BOLD,
			"{STRIKETHROUGH}" => TF::STRIKETHROUGH,
			"{UNDERLINE}" => TF::UNDERLINE,
			"{ITALIC}" => TF::ITALIC,
			"{RESET}" => TF::RESET,
		];

		return self::formatVariable($text, $list);
	}

	public static function formatVariable(string $text, array $replacements) : string {
		$text = str_replace(array_keys($relacements), array_values($replacements), $text);

		return $text;
	}

	public function textToHex(string $hex) {
		// why this??
		$hex = str_replace("#", "", $hex);

		return 00 . "x" . strtoupper($hex);
	}
}