<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Optimizer;


use Nubium\SentenceScoring\Internal\Dictionary\IWord;

interface IOptimizer
{
	public function getSentence(): string;

	/**
	 * @return IWord[]
	 */
	public function getWordsByCategory(string $category): array;


	public function removeWord(IWord $word): void;
}
