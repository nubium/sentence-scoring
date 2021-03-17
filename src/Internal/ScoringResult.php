<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal;


use Nubium\SentenceScoring\Edge\IScoringResult;

class ScoringResult implements IScoringResult
{
	protected int $score;
	protected string $hardcoreLevel;
	protected int $version;


	public function __construct(int $score, string $hardcoreLevel, int $version)
	{
		$this->score = $score;
		$this->hardcoreLevel = $hardcoreLevel;
		$this->version = $version;
	}


	public function getScore(): int
	{
		return $this->score;
	}

	public function getHardcoreLevel(): string
	{
		return $this->hardcoreLevel;
	}

	public function getVersion(): int
	{
		return $this->version;
	}
}
