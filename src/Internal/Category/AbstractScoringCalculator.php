<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Category;


use Nubium\SentenceScoring\Edge\Search\IFoundWord;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Internal\Dictionary\IWord;
use Nubium\SentenceScoring\Internal\Optimizer\IOptimizer;
use Nubium\SentenceScoring\Internal\Search\FoundWord;

abstract class AbstractScoringCalculator implements IScoringCategoryCalculator
{
	protected IOptimizer $optimizer;
	protected IDictionary $dictionary;

	/** @var IFoundWord[]|null */
	protected ?array $foundWords = null;


	public function __construct(IOptimizer $optimizer, IDictionary $dictionary)
	{
		$this->optimizer = $optimizer;
		$this->dictionary = $dictionary;
	}


	/**
	 * Workhorse method.
	 *
	 * @return IFoundWord[]
	 */
	abstract protected function apply(): array;


	public function getResult(): int
	{
		return count($this->getFoundWords());
	}

	/**
	 * @return IFoundWord[]
	 */
	public function getFoundWords(): array
	{
		if (is_null($this->foundWords)) {
			$this->foundWords = $this->apply();
		}

		return $this->foundWords;
	}


	protected function createFoundWord(IWord $word): IFoundWord
	{
		return new FoundWord($word);
	}
}
