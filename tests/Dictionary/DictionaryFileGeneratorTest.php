<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Tests\Dictionary;

use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Internal\Dictionary\DictionaryFileGenerator;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionaryPaths;
use PHPUnit\Framework\TestCase;

class DictionaryFileGeneratorTest extends TestCase
{
	public function testCreateDictionaryFile(): void
	{
		$filepath = sys_get_temp_dir() . '/testCreateDictionaryFile_' . rand(1000, 9999) . '.php';

		$dictionaryPaths = $this->createMock(IDictionaryPaths::class);
		$dictionaryPaths->method('getCsvFilePath')->willReturn(__DIR__ . '/fixtures/dictionary.123.csv');
		$dictionaryPaths->method('getGeneratedFilePath')->willReturn($filepath);

		$generator = new DictionaryFileGenerator($dictionaryPaths);
		$generator->createDictionaryFile();

		$dictionary = include($filepath);
		unlink($filepath);

		$this->assertInstanceOf(IDictionary::class, $dictionary);

		$words = $dictionary->getWordsByCategory(ISentenceScoringService::CATEGORY_1);

		$this->assertCount(4, $words);
	}
}
