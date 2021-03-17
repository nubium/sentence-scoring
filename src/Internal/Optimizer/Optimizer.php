<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Optimizer;


use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Internal\Dictionary\IWord;

class Optimizer implements IOptimizer
{
	protected string $sentence;
	protected IDictionary $dictionary;

	/** @var string[] */
	protected array $sentenceBoundaries;

	/** @var string[] */
	protected array $sentenceSingleWords;

	/** @var array<string,array<string,IWord>> */
	protected array $wordsByCategory = [];


	public function __construct(string $sentence, IDictionary $dictionary)
	{
		$this->sentence = $sentence;
		$this->dictionary = $dictionary;

		$this->prepareSentenceParts($sentence);

		$this->prepareIndices();
	}


	protected function prepareSentenceParts(string $sentence): void
	{
		$this->sentenceSingleWords = [];
		$this->sentenceBoundaries = [];

		$parts = explode(' ', $sentence);
		$lastPart = null;
		foreach ($parts as $part) {
			// single words to match
			$this->sentenceSingleWords[] = $part;

			// word boundaries to match
			if (!is_null($lastPart)) {
				// posledne pismeno slova + prve pismeno dalsieho
				$this->sentenceBoundaries[] = substr($lastPart, -1) . ' ' . substr($part, 0, 1);
			}
			$lastPart = $part;
		}
	}

	/**
	 * Pripravi zoznam slov, ktore je potencialne mozne aplikovat na nasu vetu $sentence.
	 */
	protected function prepareIndices(): void
	{
		$this->wordsByCategory = [];

		// single slova
		$singleWords = $this->dictionary->getSingleWords();
		foreach ($this->sentenceSingleWords as $sentenceSingleWord) {
			if (isset($singleWords[$sentenceSingleWord])) {
				/** @var IWord[] $words */
				$words = $singleWords[$sentenceSingleWord];
				foreach ($words as $word) {
					$this->wordsByCategory[$word->getCategory()][$word->getWord()] = $word;
				}
			}
		}

		// hranice slov
		$boundaries = $this->dictionary->getWordBoundaries();
		foreach ($this->sentenceBoundaries as $sentenceBoundary) {
			if (isset($boundaries[$sentenceBoundary])) {
				/** @var IWord[] $words */
				$words = $boundaries[$sentenceBoundary];
				foreach ($words as $word) {
					$this->wordsByCategory[$word->getCategory()][$word->getWord()] = $word;
				}
			}
		}

		// substringy
		$substringWords = $this->dictionary->getSubstringWords();
		foreach ($substringWords as $word) {
			if ($word->isSubstringOf($this->sentence) >= 0) {
				$this->wordsByCategory[$word->getCategory()][$word->getWord()] = $word;
			}
		}
	}


	public function getSentence(): string
	{
		return $this->sentence;
	}

	/**
	 * @return array<string,IWord>
	 */
	public function getWordsByCategory(string $category): array
	{
		return (isset($this->wordsByCategory[$category])) ? $this->wordsByCategory[$category] : [];
	}

	public function removeWord(IWord $word): void
	{
		$w = $word->getWord();
		$this->sentence = (string)preg_replace(
			'/(^[^ ]*| [^ ]*)' . preg_quote($w, '/') . '([^ ]* |[^ ]*$)/',
			'$1' . str_repeat('*', mb_strlen($w)) . '$2',
			$this->sentence
		);
	}
}
