<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Tests\Dictionary;

use Nubium\SentenceScoring\Internal\Dictionary\DictionaryFactory;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionaryPaths;
use PHPUnit\Framework\TestCase;

class DictionaryFactoryTest extends TestCase
{
	public function testGetChangedWords(): void
	{
		$dictionaryPaths = $this->createMock(IDictionaryPaths::class);
		$dictionaryPaths->method('getCsvFilePath')->willReturnCallback(fn ($version) => __DIR__ . "/fixtures/dictionary.$version.csv");
		$dictionaryFactory = new DictionaryFactory($dictionaryPaths, false);

		$changes = $dictionaryFactory->getChangedWords(123, 122);

		sort($changes);
		$this->assertEquals([
			'amater',
			'amateur',
			'americanka',
			'americanky',
			'andrew blake',
			'andrew rivera',
			'andy san dimas',
		], $changes);
	}
}
