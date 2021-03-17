<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;


use Nubium\SentenceScoring\Edge\Dictionary\Word;
use Nubium\SentenceScoring\Edge\Exception\ScoringException;
use Nubium\SentenceScoring\Edge\ISentenceScoringService;

class CsvDictionary extends AbstractDictionary
{
	const
		CSV_COLUMN_INDEX_WORD = 0,
		CSV_COLUMN_INDEX_CATEGORY = 1,
		CSV_COLUMN_INDEX_MATCHTYPE = 2;

	/** @var array<string,string> */
	protected array $categoryTranslateTable = [
		'L0' => ISentenceScoringService::CATEGORY_0,
		'L1' => ISentenceScoringService::CATEGORY_1,
		'L2' => ISentenceScoringService::CATEGORY_2,
		'L3' => ISentenceScoringService::CATEGORY_3,
		'L4' => ISentenceScoringService::CATEGORY_4,
		'L5' => ISentenceScoringService::CATEGORY_5,
	];


	public function __construct(string $csvFilePath)
	{
		$this->loadData($csvFilePath);
	}


	protected function loadData(string $csvFilePath): void
	{
		if ($vocabFile = fopen($csvFilePath, 'r')) {
			while ($line = fgetcsv($vocabFile)) {
				$lineCount = count($line);
				if ($lineCount !== 3) {
					throw new ScoringException(
						'Incorrect csv input file. Wrong column count in line: '
						. json_encode($line)
						. ' from file: ' . $csvFilePath
					);
				}

				$word = new Word(
					$line[self::CSV_COLUMN_INDEX_WORD],
					$this->translateCategory($line[self::CSV_COLUMN_INDEX_CATEGORY]),
					(int)$line[self::CSV_COLUMN_INDEX_MATCHTYPE]
				);

				$this->addWordToSets($word);
			}
			fclose($vocabFile);
		}
	}

	protected function translateCategory(string $csvCategory): string
	{
		return $this->categoryTranslateTable[$csvCategory];
	}
}
