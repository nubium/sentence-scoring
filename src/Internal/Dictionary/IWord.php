<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;

interface IWord
{
	public function getWord(): string ;

	public function getCategory(): string;

	public function getMatchType(): int;

	public function isSubstringOf(string $sentence): bool;
}
