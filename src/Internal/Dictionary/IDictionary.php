<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;

interface IDictionary
{
	/**
	 * @return IWord[]
	 */
	public function getWordsByCategory(string $category): array;

	/**
	 * @return array<string, IWord[]>
	 */
	public function getSingleWords(): array;

	/**
	 * @return array<string, IWord[]>
	 */
	public function getWordBoundaries(): array;

	/**
	 * @return IWord[]
	 */
	public function getSubstringWords(): array;
}
