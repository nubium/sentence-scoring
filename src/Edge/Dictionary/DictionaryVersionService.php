<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Dictionary;


use Nubium\SentenceScoring\Internal\Dictionary\IDictionaryFactory;
use Nubium\SentenceScoring\Internal\ScoringService;

class DictionaryVersionService
{
	private IDictionaryFactory $dictionaryFactory;


	public function __construct(IDictionaryFactory $dictionaryFactory)
	{
		$this->dictionaryFactory = $dictionaryFactory;
	}


	/**
	 * @return string[]
	 */
	public function getPhrasesChangedSinceLastVersion(): array
	{
		return $this->dictionaryFactory->getChangedWords($this->getCurrentVersion(), $this->getCurrentVersion() - 1);
	}

	public function getCurrentVersion(): int
	{
		return ScoringService::VERSION;
	}
}
