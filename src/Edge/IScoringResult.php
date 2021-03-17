<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge;

interface IScoringResult
{
	public function getScore(): int;

	public function getHardcoreLevel(): string;

	public function getVersion(): int;
}
