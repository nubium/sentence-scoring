<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Dictionary;


use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Internal\Dictionary\IWord;

class Word implements IWord
{
	protected string $word;
	protected string $category;
	protected int $matchType;


	public function __construct(string $word, string $category, int $matchType)
	{
		$this->word = $word;
		$this->category = $category;
		$this->matchType = $matchType;
	}


	public function getWord(): string
	{
		return $this->word;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function getMatchType(): int
	{
		return $this->matchType;
	}

	public function isSubstringOf(string $sentence): bool
	{
		$word = $this->word;
		if ($this->matchType == ISentenceScoringService::MATCHTYPE_FULLMATCH) {
			$word = ' ' . $word . ' ';
		}

		return (bool)(strpos(' ' . $sentence . ' ', $word) !== false);
	}


	/**
	 * @param array{'word': string, 'category': string, 'matchType': int} $state
	 *
	 * @return Word @this
	 */
	public static function __set_state($state)
	{
		return new self($state['word'], $state['category'], $state['matchType']);
	}
}
