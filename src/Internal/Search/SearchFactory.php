<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Search;


use Nubium\SentenceScoring\Edge\Bridge\IPrepareKeywords;
use Nubium\SentenceScoring\Edge\Search\ISearch;
use Nubium\SentenceScoring\Edge\Search\ISearchFactory;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionaryFactory;

class SearchFactory implements ISearchFactory
{
	protected IDictionaryFactory $dictionaryFactory;
	protected IPrepareKeywords $keywordPrepare;

	/** @var ISearch[] */
	protected array $searches = [];


	public function __construct(IDictionaryFactory $dictionaryFactory, IPrepareKeywords $keywordPrepare)
	{
		$this->dictionaryFactory = $dictionaryFactory;
		$this->keywordPrepare = $keywordPrepare;
	}


	public function getSearch(string $sentence): ISearch
	{
		if (!isset($this->searches[$sentence])) {
			$this->searches[$sentence] = new Search(
				$this->dictionaryFactory->getDictionary(),
				$this->keywordPrepare,
				$sentence
			);
		}

		return $this->searches[$sentence];
	}
}
