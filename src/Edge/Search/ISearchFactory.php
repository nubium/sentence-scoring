<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Search;

interface ISearchFactory
{
	public function getSearch(string $sentence): ISearch;
}
