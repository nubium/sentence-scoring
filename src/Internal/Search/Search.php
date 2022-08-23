<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Search;


use Nubium\SentenceScoring\Edge\Bridge\IPrepareKeywords;
use Nubium\SentenceScoring\Edge\Exception\ScoringException;
use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Edge\Search\IFoundWord;
use Nubium\SentenceScoring\Edge\Search\ISearch;
use Nubium\SentenceScoring\Internal\Category\IScoringCategoryCalculator;
use Nubium\SentenceScoring\Internal\Category\ScoringCategoryCalculator;
use Nubium\SentenceScoring\Internal\Category\ScoringCategoryCleanupCalculator;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Internal\Optimizer\IOptimizer;
use Nubium\SentenceScoring\Internal\Optimizer\Optimizer;

class Search implements ISearch
{
	protected string $originalSentence;
	protected ?string $preparedSentence = null;
	protected IDictionary $dictionary;
	protected IPrepareKeywords $keywordPrepare;

	/** @var array<string> poradi, v kterem se vyhledava v kategoriich */
	protected static array $categoriesOrder = [
		ISentenceScoringService::CATEGORY_0,    // nejdriv jdou goodwordy
		ISentenceScoringService::CATEGORY_5,    // potom badwordy
		ISentenceScoringService::CATEGORY_4,
		ISentenceScoringService::CATEGORY_3,    // trojka odmaze nektera slova a ostatni je uz nepouzivaji
		ISentenceScoringService::CATEGORY_1,
		ISentenceScoringService::CATEGORY_2,
	];

	/** @var array<string, IFoundWord[]> */
	protected array $foundWords = [];

	/** @var array<string, IScoringCategoryCalculator>|null */
	protected ?array $calculators = null;


	public function __construct(IDictionary $dictionary, IPrepareKeywords $keywordPrepare, string $sentence)
	{
		$this->dictionary = $dictionary;
		$this->keywordPrepare = $keywordPrepare;
		$this->originalSentence = (string)$sentence;
	}


	public function getOriginalSentence(): string
	{
		return $this->originalSentence;
	}

	public function getPreparedSentence(): string
	{
		if ($this->preparedSentence === null) {
			$this->preparedSentence = $this->keywordPrepare->stripToKeywords($this->originalSentence);
		}

		return $this->preparedSentence;
	}


	/**
	 * @return IFoundWord[]
	 */
	public function findWordsInCategory(string $category): array
	{
		if (!isset($this->foundWords[$category])) {
			$this->prepareCategoryCalculators();
			if ($this->calculators === null) {
				throw new ScoringException('Calculators are not prepared.');
			}
			foreach (static::$categoriesOrder as $cat) {
				$this->foundWords[$cat] = $this->calculators[$cat]->getFoundWords();
				if ($cat === $category) {
					// nasli jsme pozadovanou kategorii netreba predcasne hledat slova
					// z dalsich kategorii
					break;
				}
			}
		}
		if (!isset($this->foundWords[$category])) {
			throw new \InvalidArgumentException('Unknown category \'' . $category . '\'.');
		}

		return $this->foundWords[$category];
	}

	/**
	 * @return array<string, IFoundWord[]>
	 */
	public function findWords(): array
	{
		foreach (static::$categoriesOrder as $cat) {
			$this->findWordsInCategory($cat);
		}
		return $this->foundWords;
	}


	protected function prepareCategoryCalculators(): void
	{
		if ($this->calculators === null) {
			$optimizer = $this->createOptimizer();
			$this->calculators = [
				ISentenceScoringService::CATEGORY_0 => new ScoringCategoryCalculator($optimizer, $this->dictionary,
					ISentenceScoringService::CATEGORY_0),
				ISentenceScoringService::CATEGORY_1 => new ScoringCategoryCalculator($optimizer, $this->dictionary,
					ISentenceScoringService::CATEGORY_1),
				ISentenceScoringService::CATEGORY_2 => new ScoringCategoryCalculator($optimizer, $this->dictionary,
					ISentenceScoringService::CATEGORY_2),
				ISentenceScoringService::CATEGORY_3 => new ScoringCategoryCleanupCalculator(clone $optimizer,
					$this->dictionary, ISentenceScoringService::CATEGORY_3),
				ISentenceScoringService::CATEGORY_4 => new ScoringCategoryCalculator($optimizer, $this->dictionary,
					ISentenceScoringService::CATEGORY_4),
				ISentenceScoringService::CATEGORY_5 => new ScoringCategoryCalculator($optimizer, $this->dictionary,
					ISentenceScoringService::CATEGORY_5),
			];
		}
	}

	protected function createOptimizer(): IOptimizer
	{
		return new Optimizer($this->getPreparedSentence(), $this->dictionary);
	}
}
