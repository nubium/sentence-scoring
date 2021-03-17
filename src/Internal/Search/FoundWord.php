<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Search;


use Nubium\SentenceScoring\Edge\Search\IFoundWord;
use Nubium\SentenceScoring\Internal\Dictionary\IWord;

class FoundWord implements IFoundWord
{
	protected IWord $word;


	public function __construct(IWord $word)
	{
		$this->word = $word;
	}


	public function getWord(): string
	{
		return $this->word->getWord();
	}

	public function getMatchType(): int
	{
		return $this->word->getMatchType();
	}
}
