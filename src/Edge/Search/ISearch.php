<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Search;

interface ISearch
{
	public function getOriginalSentence(): string;

	public function getPreparedSentence(): string;


	/**
	 * @return IFoundWord[]
	 */
	public function findWordsInCategory(string $category): array;

	/**
	 * @return array<string, IFoundWord[]> category is key
	 */
	public function findWords(): array;
}
