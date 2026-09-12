<?php
namespace App\Domains\Employee\ValueObjects;
use Carbon\CarbonImmutable;
final readonly class DateRange { public function __construct(public CarbonImmutable $from,public ?CarbonImmutable $to=null){if($to&&$to->lessThan($from))throw new \InvalidArgumentException('End date must not precede start date.');} public function contains(CarbonImmutable $date):bool{return $date->greaterThanOrEqualTo($this->from)&&(!$this->to||$date->lessThanOrEqualTo($this->to));} }
