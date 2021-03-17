<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Search;

interface IFoundWord
{
	public function getWord(): string;

	public function getMatchType(): int;
}
