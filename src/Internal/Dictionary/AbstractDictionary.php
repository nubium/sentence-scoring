<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;


use Nubium\SentenceScoring\Edge\ISentenceScoringService;

abstract class AbstractDictionary implements IDictionary
{
	/** @var array<string, IWord[]> */
	protected array $singleWords = [];

	/** @var array<string, IWord[]> */
	protected array $wordsByCategory = [];

	/** @var array<string, IWord[]> */
	protected array $wordBoundaries = [];

	/** @var IWord[] */
	protected array $substringWords = [];


	protected function addWordToSets(IWord $word): void
	{
		$this->wordsByCategory[$word->getCategory()][$word->getWord()] = $word;

		$parts = explode(' ', $word->getWord());
		$lastPart = null;
		foreach ($parts as $part) {
			// single slova
			$this->singleWords[$part][] = $word;

			// hranice slov
			if (!is_null($lastPart)) {
				// posledne pismeno slova + prve pismeno dalsieho
				$this->wordBoundaries[substr($lastPart, -1) . ' ' . substr($part, 0, 1)][] = $word;
			}
			$lastPart = $part;
		}

		// substringy sa nedaju matchovat len cez '==' porovnanie dvoch slov, takze sa im musime venovat extra
		// v pripade ze to je jednoslovne, nedokazeme to matchnut ani cez hranice slov
		// zostava nam len hladat cez substring
		if ($word->getMatchType() == ISentenceScoringService::MATCHTYPE_SUBSTRING
			&& count($parts) == 1
		) {
			$this->substringWords[] = $word;
		}
	}


	/**
	 * @return IWord[]
	 */
	public function getWordsByCategory(string $category): array
	{
		return $this->wordsByCategory[$category];
	}

	/**
	 * @return array<string, IWord[]>
	 */
	public function getWordBoundaries(): array
	{
		return $this->wordBoundaries;
	}

	/**
	 * @return array<string, IWord[]>
	 */
	public function getSingleWords(): array
	{
		return $this->singleWords;
	}

	/**
	 * @return IWord[]
	 */
	public function getSubstringWords(): array
	{
		return $this->substringWords;
	}
}
