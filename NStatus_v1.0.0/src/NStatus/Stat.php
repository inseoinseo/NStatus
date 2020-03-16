<?php


namespace NStatus;



class Stat
{
	
	protected $data = [];
	
	
	public function __construct (array $data)
	{
		$this->data = $data;
	}
	
	public function getDataArray (): array
	{
		return (array) $this->data;
	}
	
	public function getStatPoint (): int
	{
		if (isset ($this->data ["statPoint"])) {
			return (int) $this->data ["statPoint"];
		}
		return 0;
	}
	
	public function getStat (string $status = "str"): int
	{
		if (isset ($this->data [$status])) {
			return (int) $this->data [$status];
		}
		return 0;
	}
	
	public function setStatPoint (int $statPoint = 0): void
	{
		$this->data ["statPoint"] = $statPoint;
	}
	
	public function setStat (string $status = "str", int $value = 0): void
	{
		$this->data [$status] = $value;
	}
	
}