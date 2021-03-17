<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal;


use Nubium\SentenceScoring\Edge\IScoringResult;
use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Edge\Search\ISearch;
use Nubium\SentenceScoring\Edge\Search\ISearchFactory;

class ScoringService implements ISentenceScoringService
{
	/** @internal */
	const VERSION = 7;

	protected ISearchFactory $searchFactory;

	/** @var array<string,array<string,int>> */
	protected array $settings = [
		'categoryCoefficients' => [
			self::CATEGORY_0 => 0,
			self::CATEGORY_1 => 1,
			self::CATEGORY_2 => 3,
			self::CATEGORY_3 => 6,
			self::CATEGORY_4 => 30,
			self::CATEGORY_5 => 100,
		],
	];

	public function __construct(ISearchFactory $searchFactory)
	{
		$this->searchFactory = $searchFactory;
	}


	public function analyzeSentence(string $sentence): IScoringResult
	{
		$search = $this->searchFactory->getSearch($sentence);

		return $this->scoreSentence($search);
	}

	public function getVersion(): int
	{
		return self::VERSION;
	}


	protected function createResult(int $score, string $hardcoreLevel): IScoringResult
	{
		return new ScoringResult(min($score, 255), $hardcoreLevel, $this->getVersion());
	}

	protected function getCategoryCoefficient(string $category): int
	{
		return $this->settings['categoryCoefficients'][$category];
	}

	protected function scoreSentence(ISearch $search): IScoringResult
	{
		// skorovani
		if ($search->findWordsInCategory(self::CATEGORY_0)) {
			// krok 1 detekce goodwords
			// vratime score 0 pokud sedi goodword a koncime
			return $this->createResult(0, self::HARDCORE_LEVEL_SAFE);
		} elseif ($search->findWordsInCategory(self::CATEGORY_5)) {
			// krok 2 detekce C5 (BAN words)
			// vratime score 666 pokud sedi L5 a koncime
			return $this->createResult(666, self::HARDCORE_LEVEL_ILLEGAL);
		}

		$cat1Count = count($search->findWordsInCategory(self::CATEGORY_1));
		$cat2Count = count($search->findWordsInCategory(self::CATEGORY_2));
		$cat3Count = count($search->findWordsInCategory(self::CATEGORY_3));
		$cat4Count = count($search->findWordsInCategory(self::CATEGORY_4));

		$cat1Score = $cat1Count * $this->getCategoryCoefficient(self::CATEGORY_1);
		$cat2Score = $cat2Count * $this->getCategoryCoefficient(self::CATEGORY_2);
		$cat3Score = $cat3Count * $this->getCategoryCoefficient(self::CATEGORY_3);
		$cat4Score = $cat4Count * $this->getCategoryCoefficient(self::CATEGORY_4);

		$sentenceScore = ($cat1Score + $cat3Score + $cat4Score) * ($cat2Score ?: 1);

		if ($cat2Count && $sentenceScore >= self::BAN_LEVEL) {
			// krok 2 BAN za C2 a vysoke skore
			return $this->createResult($sentenceScore, self::HARDCORE_LEVEL_ILLEGAL);
		} elseif ($cat4Count) {
			// krok 3 detekce PORN
			return $this->createResult($sentenceScore, self::HARDCORE_LEVEL_PORN);
		} elseif ($cat4Count === 0 && $sentenceScore < self::PORN_LEVEL) {
			// krok 4 detekce SAFE
			return $this->createResult($sentenceScore, self::HARDCORE_LEVEL_SAFE);
		}

		// krok 5 vse ostatni
		return $this->createResult($sentenceScore, self::HARDCORE_LEVEL_PORN);
	}
}
