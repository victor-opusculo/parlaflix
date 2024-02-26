<?php
namespace VictorOpusculo\Parlaflix\Components\Data;

class FixedParameter
{
	private $value;

	public function __construct($parameterValue)
	{
		$this->value = $parameterValue;
	}

	public function __toString() : string
	{
		return $this->value;
	}
}