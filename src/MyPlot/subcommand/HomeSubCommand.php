<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\Plot;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HomeSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 *
	 * @return bool
	 */
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.home");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		if(empty($args)) {
			$selected = $sender;
		    $selectedName = $sender->getName();
			$plotNumber = 1;
		}elseif(isset($args[0]) and ($selected = $this->getPlugin()->getServer()->getPlayer($args[0])) !== null) {
			$selectedName = $selected->getName();
			if(stripos($selectedName, "me") !== false) {
				$selectedName = $sender->getName();
			}
			if(!isset($args[1]) or !is_numeric($args[1]))
				return false;
			$plotNumber = (int) $args[1];
		}else{
			return false;
		}
		$levelName = $args[2] ?? $sender->getLevel()->getFolderName();
		$plots = $this->getPlugin()->getPlotsOfPlayer($selectedName, $levelName);
		if(empty($plots)) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.noplots"));
			return true;
		}
		if(!isset($plots[$plotNumber - 1])) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.notexist", [$plotNumber]));
			return true;
		}
		usort($plots, function(Plot $plot1, Plot $plot2) {
			if($plot1->levelName == $plot2->levelName) {
				return 0;
			}
			return ($plot1->levelName < $plot2->levelName) ? -1 : 1;
		});
		$plot = $plots[$plotNumber - 1];
		if($this->getPlugin()->teleportPlayerToPlot($selected, $plot)) {
			$sender->sendMessage($this->translateString("home.success", [$plot, $plot->levelName]));
		}else{
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.error"));
		}
		return true;
	}
}