<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;

interface IDictionaryFactory
{
	public function getDictionary(): IDictionary;

	/**
	 * @return string[]
	 */
	public function getChangedWords(int $version, int $refVersion): array;
}
