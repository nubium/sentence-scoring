<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;


use Nubium\SentenceScoring\Internal\ScoringService;

class DictionaryFileGenerator
{
	protected IDictionaryPaths $dictionaryPaths;


	public function __construct(IDictionaryPaths $dictionaryPaths)
	{
		$this->dictionaryPaths = $dictionaryPaths;
	}


	/**
	 * Vytvori soubor, z ktereho se nacita Dictionary v metode getDictionary
	 */
	public function createDictionaryFile(): void
	{
		$csvPath = $this->dictionaryPaths->getCsvFilePath(ScoringService::VERSION);
		$dictionary =  new CsvDictionary($csvPath);

		$code = [];
		$code[] = "<?php\r\n";
		$code[] = "return unserialize('" . serialize($dictionary) . "');\r\n";

		file_put_contents($this->dictionaryPaths->getGeneratedFilePath(), $code);
	}
}
