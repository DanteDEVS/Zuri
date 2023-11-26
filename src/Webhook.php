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

use pocketmine\player\Player;
use pocketmine\utils\Config;
use Zuri\Libraries\DiscordWebhookAPI\Webhook as DiscordWebhookAPI;
use Zuri\Libraries\DiscordWebhookAPI\Embed;
use Zuri\Libraries\DiscordWebhookAPI\Message;
use Zuri\Libraries\DiscordWebhookAPI\AllowedMentions;
use Zuri\Anticheat;
use Zuri\Utils;

class Webhook {
	
	public const PLAYER_WARNING = 0;
	public const PLAYER_KICK = 1;
	
	public ?DiscordWebhookAPI $webhook = null;
	public ?Config $config = null;
	
	public function getWebhook() : DiscordWebhookAPI{
		if($this->webhook === null){
			$this->webhook = new Webhook($this->getConfig()->get("webhook-url"));
		}
		
		return $this->webhook;
	}
	
	public function getConfig() : Config{
		if($this->config === null){
			$this->config = new Config(Anticheat::getInstance()->getDataFolder() . "webhook.yml", Config::YAML);
		}
		
		return $this->config;
	}
	
	
	public function sendEmbed(Player $player, string $module, int $type = Webhook::PLAYER_WARNING) : void{
		$message = new Message();
		$embed = new Embed();
		$mentions = new AllowedMentions();
		
		$message->setUsername($this->getConfig()->getNested("webhook-info.name"));
		$message->setAvatarUrl($this->getConfig()->getNested("webhook-info.avatar_url"));
		$mentions->mentionEveryone($this->getConfig()->getNested("webhook-info.alwaysMentionEveryone"));
		
		if($this->getConfig()->getNested("webhook-info.mention_users.enabled")){
			foreach($this->getConfig()->getNested("webhook-info.mention_users.value") as $userId){
				$mentions->addUser($userId);
			}
		}
		
		if($this->getConfig()->getNested("webhook-info.mention_roles.enabled")){
			foreach($this->getConfig()->getNested("webhook-info.mention_roles.value") as $roleId){
				$mentions->addRole($roleId);
			}
		}
		
		if($type === Webhook::PLAYER_WARNING){
			if($this->getConfig()->getNested("warn.message.enabled") === true){
				$message->setContent($this->format($this->getConfig()->getNested("warn.message.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]));
			}
			
			if($this->getConfig()->getNested("warn.embed.enabled") === true){
				if($this->getConfig()->getNested("warn.embed.color.enabled") === true){
					$embed->setColor(Utils::textToHex($this->getConfig()->getNested("warn.embed.color.value")));
				}
				
				if($this->getConfig()->getNested("warn.embed.title.enabled") === true){
					$embed->setTitle($this->format($this->getConfig()->getNested("warn.embed.title.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]));
				}
				
				if($this->getConfig()->getNested("warn.embed.footer.enabled") === true){
					if($this->getConfig()->getNested("warn.embed.footer.icon_url.enabled") === true){
						$embed->setFooter($this->format($this->getConfig()->getNested("warn.embed.footer.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]), $this->getConfig()->getNested("warn.embed.footer.icon_url.vaue"));
					} else {
						$embed->setFooter($this->format($this->getConfig()->getNested("warn.embed.footer.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]), null);
					}
				}
				
				if($this->getConfig()->getNested("warn.embed.description.enabled") === true){
					$text = implode("\n", $this->getConfig()->getNested("warn.embed.description.value"));
					$embed->setDescription($this->format($text, ["{player_name}" => $player->getName(), "{module_name}" => $module]));
				}
				
				if($this->getConfig()->getNested("warn.embed.image.enabled") === true){
					foreach($this->getConfig()->getNested("warn.embed.image.value") as $image){
						$embed->setImage($image);
					}
				}
				
				if($this->getConfig()->getNested("warn.embed.thumbnail.enabled") === true){
					$embed->setImage($this->getConfig()->getNested("warn.embed.thumbnail.value"));
				}
			}
			
		} else {
			if($this->getConfig()->getNested("kick.message.enabled") === true){
				$message->setContent($this->format($this->getConfig()->getNested("kick.message.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]));
			}
			
			if($this->getConfig()->getNested("kick.embed.enabled") === true){
				if($this->getConfig()->getNested("kick.embed.color.enabled") === true){
					$embed->setColor(Utils::textToHex($this->getConfig()->getNested("warn.embed.color.value")));
				}
				
				if($this->getConfig()->getNested("kick.embed.title.enabled") === true){
					$embed->setTitle($this->format($this->getConfig()->getNested("kick.embed.title.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]));
				}
				
				if($this->getConfig()->getNested("kick.embed.footer.enabled") === true){
					if($this->getConfig()->getNested("kick.embed.footer.icon_url.enabled") === true){
						$embed->setFooter($this->format($this->getConfig()->getNested("kick.embed.footer.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]), $this->getConfig()->getNested("kick.embed.footer.icon_url.vaue"));
					} else {
						$embed->setFooter($this->format($this->getConfig()->getNested("kick.embed.footer.value"), ["{player_name}" => $player->getName(), "{module_name}" => $module]), null);
					}
				}
				
				if($this->getConfig()->getNested("kick.embed.description.enabled") === true){
					$text = implode("\n", $this->getConfig()->getNested("kick.embed.description.value"));
					$embed->setDescription($this->format($text, ["{player_name}" => $player->getName(), "{module_name}" => $module]));
				}
				
				if($this->getConfig()->getNested("kick.embed.image.enabled") === true){
					foreach($this->getConfig()->getNested("kick.embed.image.value") as $image){
						$embed->setImage($image);
					}
				}
				
				if($this->getConfig()->getNested("kick.embed.thumbnail.enabled") === true){
					$embed->setImage($this->getConfig()->getNested("kick.embed.thumbnail.value"));
				}
			}
		}
	}
	
	public function format(string $text, array $replacements) :string{
		$text = str_replace(array_keys($relacements), array_values($replacements), $text);
		
		return $text;
	}
}