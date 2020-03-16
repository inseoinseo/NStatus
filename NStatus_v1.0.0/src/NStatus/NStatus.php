<?php


namespace NStatus;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use NStatus\command\StatCommand;

class NStatus extends PluginBase
{
	
	private static $instance = null;
	
	public static $prefix = "§l§6[알림]§r§7 ";
	
	public $config, $db;
	
	public static $stat = [];
	
	
	public static function runFunction (): NStatus
	{
		return self::$instance;
	}
	
	public function onLoad (): void
	{
	   self::$instance = $this;
		if (!file_exists ($this->getDataFolder ())) {
			@mkdir ($this->getDataFolder ());
		}
		$this->config = new Config ($this->getDataFolder () . "config.yml", Config::YAML, [
			"player" => []
		]);
		$this->db = $this->config->getAll ();
		
		foreach ($this->db ["player"] as $playerName => $data) {
			self::$stat [$playerName] = new Stat ($data);
		}
	}
	
	public function onEnable (): void
	{
		$this->getServer ()->getCommandMap ()->registerAll ("avas", [
			new StatCommand ($this)
		]);
	}
	
	public static function message ($player, string $msg): void
	{
		$player->sendMessage (self::$prefix . $msg);
	}
	
	public function onDisable (): void
	{
		foreach (self::$stat as $playerName => $class) {
			if ($class instanceof Stat) {
				$this->db ["player"] [$playerName] = $class->getDataArray ();
			}
		}
		if ($this->config instanceof Config) {
		$this->config->setAll ($this->db);
		$this->config->save ();
		}
	}
	
	public function isPlayerData (string $name): bool
	{
		return isset ($this->db ["player"] [$name]);
	}
	
	public function addPlayerData (string $name): void
	{
		$this->db ["player"] [$name] = [
			"statPoint" => 3,
			"hp" => 0,
			"str" => 0,
			"def" => 0,
			"dex" => 0,
			"stp" => 0,
			"ctk" => 0
		];
		self::$stat [$name] = new Stat ($this->db ["player"] [$name]);
	}
	
	public static function getStat (string $name): ?Stat
	{
		return isset (self::$stat [$name]) ? self::$stat [$name] : null;
	}
	
	public function addStatPoint (string $name, int $stat): void
	{
		if (($class = self::getStat ($name)) instanceof Stat) {
			$class->setStatPoint ($class->getStatPoint () + $stat);
		}
	}
	
}