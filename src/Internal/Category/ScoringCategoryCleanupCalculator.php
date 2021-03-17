<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Category;


use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Internal\Optimizer\IOptimizer;

class ScoringCategoryCleanupCalculator extends AbstractScoringCalculator
{
	protected string $category;


	public function __construct(IOptimizer $optimizer, IDictionary $dictionary, string $category)
	{
		parent::__construct($optimizer, $dictionary);

		$this->category = $category;
	}


	protected function apply(): array
	{
		$foundWords = [];
		foreach ($this->optimizer->getWordsByCategory($this->category) as $word) {
			if ($word->isSubstringOf($this->optimizer->getSentence())) {
				$this->optimizer->removeWord($word);
				$foundWords[] = $this->createFoundWord($word);
			}
		}

		return $foundWords;
	}
}
