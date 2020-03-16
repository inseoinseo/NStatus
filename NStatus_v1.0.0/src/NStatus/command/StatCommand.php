<?php


namespace NStatus\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use NStatus\NStatus;
use NStatus\Stat;
use CustomUI\CustomUI;

use NEquip\NEquip;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;

class StatCommand extends Command implements Listener
{
	
	/** @var null|NStatus */
	protected $plugin = null;
	
	/** @var string */
	public const STAT_COMMAND_PERMISSION = "user";
	
	/** @var array */
	private $mode = [];
	
	
	public function __construct (NStatus $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("스탯", "스탯 명령어 입니다.");
		$this->setPermission (self::STAT_COMMAND_PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $this->plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			$handle = CustomUI::runFunction ()->SimpleForm (function (Player $player, array $data) {
				if (!isset ($data [0])) {
					return false;
				}
				if ($data [0] === 0) $this->sendStatInfoMenu ($player);
				if ($data [0] === 1) $this->sendStatUpMenu ($player);
				//if ($data [0] === 2) $this->sendStatMyInfoMenu ($player);
			});
			$handle->setTitle ("§l스탯");
			//$handle->setContent ("사용할 기능을 선택 해주세요.");
			$handle->addButton ("§l스탯 정보\n§r§8스탯 정보를 봅니다.");
			$handle->addButton ("§l스탯 올리기\n§r§8스탯을 올립니다.");
			//$handle->addButton ("§l스탯 내정보\n§r§8내 스탯 정보를 봅니다.");
			$handle->sendToPlayer ($player);
		} else {
			NStatus::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
	
	public function sendMessage (Player $player, string $msg): void
	{
		$handle = CustomUI::runFunction ()->ModalForm (function (Player $player, array $data) {
			if (!isset ($data [0])) {
				return false;
			}
			if (isset ($this->mode [$player->getName ()])) {
				if ($this->mode [$player->getName ()] === "statinfo") {
					if ($data [0] === 0) $this->sendStatInfoMenu ($player);
				}
			} else {
				return false;
			}
		});
		$handle->setTitle ("§l스탯");
		$handle->setContent ("{$msg}");
		if (isset ($this->mode [$player->getName ()])) {
			if ($this->mode [$player->getName ()] === "statinfo") {
				$handle->setButton1 ("§l뒤로가기");
				$handle->setButton2 ("§l닫기");
			} else {
				$handle->setButton1 ("§l창닫기");
				$handle->setButton2 ("§l창닫기");
			}
		}
		$handle->sendToPlayer ($player);
	}
	
	public function sendStatInfoMenu (Player $player): void
	{
		$this->mode [$player->getName ()] = "statinfo";
		$handle = CustomUI::runFunction ()->SimpleForm (function (Player $player, array $data) {
			if (!isset ($data [0])) {
				return false;
			}
			if ($data [0] === 0)
				$this->sendMessage ($player, "[ HP 스탯 ] [ 체력 스탯 ]\n     ㄴ 체력 스탯 10당 체력 반칸 (1)씩 증가합니다.");
			else if ($data [0] === 1)
				$this->sendMessage ($player, "[ STR 스탯 ] [ 공격력 스탯 ]\n     ㄴ 공격력 스탯 5당 1데미지씩 증가합니다.");
			else if ($data [0] === 2)
				$this->sendMessage ($player, "[ DEF 스탯 ] [ 방어력 스탯 ]\n     ㄴ 방어력 스탯 10당 1방어력씩 증가합니다.");
			else if ($data [0] === 3)
				$this->sendMessage ($player, "[ DEX 스탯 ] [ 민첩 스탯 ]\n     ㄴ 민첩 스탯 1당 크리티컬 추가 데미지 1씩 증가합니다.");
			else if ($data [0] === 4)
				$this->sendMessage ($player, "[ STP 스탯 ] [ 흡혈 스탯 ]\n     ㄴ 흡혈 스탯 10당 0.2 체력 흡수 증가");
			else if ($data [0] === 5)
				$this->sendMessage ($player, "[ CTK 스탯 ] [ 크리티컬 스탯 ]\n     ㄴ 크리티컬 스탯 20당 확률 1% 증가 (100%시 50%로 고정)");
		});
		$handle->setTitle ("§l스탯");
		$handle->setContent ("사용할 기능을 선택 해주세요.");
		$handle->addButton ("§lHP 스탯 정보");
		$handle->addButton ("§lSTR 스탯 정보");
		$handle->addButton ("§lDEF 스탯 정보");
		$handle->addButton ("§lDEX 스탯 정보");
		$handle->addButton ("§lSTP 스탯 정보");
		$handle->addButton ("§lCTK 스탯 정보");
		$handle->sendToPlayer ($player);
	}
	
	public function sendStatUpMenu (Player $player): void
	{
		$handle = CustomUI::runFunction ()->SimpleForm (function (Player $player, array $data) {
			if (!isset ($data [0])) {
				return false;
			}
			$stat = [ "hp", "str", "def", "dex", "stp", "ctk" ];
			if (isset ($stat [$data [0]])) {
				$class = NStatus::getStat ($player->getName ());
				if ($class instanceof Stat) {
					if ($class->getStatPoint () >= 1) {
						$class->setStatPoint ($class->getStatPoint () - 1);
						$class->setStat ($stat [$data [0]], $class->getStat ($stat [$data [0]]) + 1);
						NStatus::message ($player, "§a" . strtoupper ($stat [$data [0]]) . "§7 스탯을 §a1§7 만큼 올리셨습니다.");
						$this->sendStatUpMenu ($player);
					} else {
						NStatus::message ($player, "보유하신 스탯포인트가 부족합니다.");
					}
				}
			} else {
				return false;
			}
		});
		$handle->setTitle ("§l스탯");
		$handle->setContent ("사용할 기능을 선택 해주세요.");
		$stat = [ "체력", "공격력", "방어력", "민첩", "흡혈", "크리티컬" ];
		foreach ($stat as $format) {
			$handle->addButton ("- {$format} 스탯 찍기");
		}
		$handle->sendToPlayer ($player);
	}
	
	public function onJoin (PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer ();
		$name = $player->getName ();
		
		if (!$this->plugin->isPlayerData ($name)) {
			$this->plugin->addPlayerData ($name);
		}
	}
	
	public function getSTR (Player $player): int
	{
		$str = 0;
		if (($class = NStatus::getStat ($player->getName ())) instanceof Stat) {
			$str += $class->getStat ("str");
		}
		$str += (int) NEquip::runFunction ()->getStatArray ($player) ["str"];
		$str = $str / 5;
		return intval ($str);
	}
	
	public function getDEF (Player $player): int
	{
		$def = 0;
		if (($class = NStatus::getStat ($player->getName ())) instanceof Stat) {
			$def += $class->getStat ("def");
		}
		$def += (int) NEquip::runFunction ()->getStatArray ($player) ["def"];
		$def = $def / 10;
		return intval ($def);
	}
	
	public function getDEX (Player $player): int
	{
		$dex = 0;
		if (($class = NStatus::getStat ($player->getName ())) instanceof Stat) {
			$dex += $class->getStat ("dex");
		}
		$dex += (int) NEquip::runFunction ()->getStatArray ($player) ["dex"];
		$dex = $dex / 10;
		return intval ($dex);
	}
	
	public function getCTK (Player $player): int
	{
		$ctk = 0;
		if (($class = NStatus::getStat ($player->getName ())) instanceof Stat) {
			$ctk += $class->getStat ("ctk");
		}
		$ctk += (int) NEquip::runFunction ()->getStatArray ($player) ["ctk"];
		$ctk = $ctk / 20;
		if ($ctk >= 100)
			$ctk = 50;
		return intval ($ctk);
	}
	
	public function getHP (Player $player): int
	{
		$hp = 0;
		if (($class = NStatus::getStat ($player->getName ())) instanceof Stat) {
			$hp += $class->getStat ("hp");
		}
		$hp += (int) NEquip::runFunction ()->getStatArray ($player) ["hp"];
		$hp = $hp / 10;
		return intval ($hp);
	}
	
	public function onMove (PlayerItemHeldEvent $event): void
	{
		$player = $event->getPlayer ();
		
		if ($this->plugin->isPlayerData ($player->getName ())) {
			$hp = 20 + $this->getHP ($player);
			$player->setMaxHealth ($hp);
		}
	}
	
	public function onMoves (PlayerMoveEvent $event): void
	{
$player = $event->getPlayer ();
		
		if ($this->plugin->isPlayerData ($player->getName ())) {
			$hp = 20 + $this->getHP ($player);
			$player->setMaxHealth ($hp);
		}
	}
	
	public function onAttack (EntityDamageEvent $event): void
	{
		if ($event instanceof EntityDamageByEntityEvent) {
			if (($player = $event->getDamager ()) instanceof Player) {
				$entity = $event->getEntity ();
				$damage = $this->getSTR ($player);
				if ($entity instanceof Player) {
					$damage -= ($this->getDEF ($entity) / 10);
				}
				if (mt_rand (1, 100) === $this->getCTK ($player)) {
					$player->sendTip ("§l§c크리티컬!");
					$event->setBaseDamage ($damage*2);
					return;
				}
				$event->setBaseDamage ($damage);
			}
		}
	}
}