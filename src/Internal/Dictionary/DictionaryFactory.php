<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;

use Nubium\SentenceScoring\Internal\ScoringService;
use RuntimeException;

class DictionaryFactory implements IDictionaryFactory
{
	protected ?IDictionary $dictionary = null;
	protected IDictionaryPaths $dictionaryPaths;
	protected bool $noCache;


	public function __construct(IDictionaryPaths $dictionaryPaths, bool $noCache)
	{
		$this->dictionaryPaths = $dictionaryPaths;
		$this->noCache = $noCache;
	}


	public function getDictionary(): IDictionary
	{
		if ($this->dictionary === null) {
			$this->dictionary = $this->noCache ? $this->createCsvDictionary() : $this->loadCsvDictionary();
		}

		return $this->dictionary;
	}


	/**
	 * Returns the differences between two versions of a dictionary.
	 *
	 * @return string[]
	 */
	public function getChangedWords(int $version, int $refVersion): array
	{
		$file = @file($this->dictionaryPaths->getCsvFilePath($version));
		if (!is_array($file) || count($file) == 0) {
			throw new \InvalidArgumentException('Invalid CSV file for version ' . $version);
		}

		$refFile = @file($this->dictionaryPaths->getCsvFilePath($refVersion));
		if (!is_array($refFile) || count($refFile) == 0) {
			throw new \InvalidArgumentException('Invalid CSV file for version ' . $refVersion);
		}

		$rawDiff = array_merge(array_diff($file, $refFile), array_diff($refFile, $file));
		$result = [];
		foreach ($rawDiff as $row) {
			$result[] = explode(',', $row, 2)[0];
		}

		return array_unique($result);
	}


	/**
	 * Creates a fresh dictionary from CSV file.
	 */
	protected function createCsvDictionary(): CsvDictionary
	{
		$csvFilePath = $this->dictionaryPaths->getCsvFilePath(ScoringService::VERSION);

		return new CsvDictionary($csvFilePath);
	}

	/**
	 * Load the pre-generated PHP class
	 */
	protected function loadCsvDictionary(): IDictionary
	{
		$generatedFilePath = $this->dictionaryPaths->getGeneratedFilePath();
		$dictionary = include($generatedFilePath);

		if ($dictionary === false) {
			throw new RuntimeException("Include SentenceScoring dictionary from '$generatedFilePath' failed. What about run 'php createDictionaryFile.php'?");
		}

		if (!$dictionary instanceof IDictionary) {
			throw new RuntimeException("'$generatedFilePath' doesn't contain instance of " . IDictionary::class);
		}

		return $dictionary;
	}
}
