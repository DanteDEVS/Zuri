<?php

namespace Zuri\Utils;

use pocketmine\utils\TextFormat as TF;

class Utils {
	
	public static function colorFormat(string $text) : string{
		
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
	
	public static function formatVariable(string $text, array $replacements) :string{
		$text = str_replace($text, array_keys($relacements), array_values($replacements), $text);
		
		return $text;
	}
	
	public function textToHex(string $hex) {
		//TODO
	}
}