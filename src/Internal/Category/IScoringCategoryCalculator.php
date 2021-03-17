<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Category;


use Nubium\SentenceScoring\Edge\Search\IFoundWord;

interface IScoringCategoryCalculator
{
	/**
	 * Vraci pocet nalezenych vyskytu.
	 */
	public function getResult(): int;

	/**
	 * @return IFoundWord[]
	 */
	public function getFoundWords(): array;
}
