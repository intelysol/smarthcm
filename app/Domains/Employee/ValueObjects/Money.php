<?php
namespace App\Domains\Employee\ValueObjects;
final readonly class Money { public function __construct(public int $minorUnits,public string $currency){if($currency===''||strlen($currency)!==3)throw new \InvalidArgumentException('Currency must be an ISO 4217 code.');} public function add(self $other):self{if($this->currency!==$other->currency)throw new \InvalidArgumentException('Currencies must match.');return new self($this->minorUnits+$other->minorUnits,$this->currency);} }
