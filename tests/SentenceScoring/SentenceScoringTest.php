<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Tests\SentenceScoring;

use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Edge\Search\IFoundWord;
use Nubium\SentenceScoring\Edge\Search\ISearch;
use Nubium\SentenceScoring\Edge\Search\ISearchFactory;
use Nubium\SentenceScoring\Internal\ScoringService;
use PHPUnit\Framework\TestCase;

class SentenceScoringTest extends TestCase
{
	/**
	 * @return array<mixed>
	 */
	public static function dpAnalyzeSentence(): array
	{
		return [
			'porn works 1' => [
				'sentence' => 'slovo0  sex slabysex slovo2',
				'words' => [
					ISentenceScoringService::CATEGORY_3 => [['sex', ISentenceScoringService::MATCHTYPE_FULLMATCH]],
					ISentenceScoringService::CATEGORY_1 => [['slabysex', ISentenceScoringService::MATCHTYPE_FULLMATCH]],
				],
				'resultsScore' => 7,
				'resultStatus' => ISentenceScoringService::HARDCORE_LEVEL_PORN,
			],
			'porn works 2' => [
				'sentence' => 'slovo0 hint1 hint2 hint3 multiplier slovo2',
				'words' => [
					ISentenceScoringService::CATEGORY_1 => [
						['hint1', ISentenceScoringService::MATCHTYPE_FULLMATCH],
						['hint2', ISentenceScoringService::MATCHTYPE_FULLMATCH],
						['hint3', ISentenceScoringService::MATCHTYPE_FULLMATCH],
					],
					ISentenceScoringService::CATEGORY_2 => [
						[
							'multiplier',
							ISentenceScoringService::MATCHTYPE_FULLMATCH,
						],
					],
				],
				'resultsScore' => 9,
				'resultStatus' => ISentenceScoringService::HARDCORE_LEVEL_PORN,
			],
			'such illegal many sentence wow' => [
				'sentence' => 'slovo0 megaprase multiply slovo2',
				'words' => [
					ISentenceScoringService::CATEGORY_2 => [['multiply', ISentenceScoringService::MATCHTYPE_FULLMATCH]],
					ISentenceScoringService::CATEGORY_4 => [
						[
							'megaprase',
							ISentenceScoringService::MATCHTYPE_FULLMATCH,
						],
					],
				],
				'resultsScore' => 90,
				'resultStatus' => ISentenceScoringService::HARDCORE_LEVEL_ILLEGAL,
			],
			'C5 direct into porn' => [
				'sentence' => 'slovo0 megaprase slovo2',
				'words' => [
					ISentenceScoringService::CATEGORY_4 => [
						[
							'megaprase',
							ISentenceScoringService::MATCHTYPE_FULLMATCH,
						],
					],
				],
				'resultsScore' => 30,
				'resultStatus' => ISentenceScoringService::HARDCORE_LEVEL_PORN,
			],
			'multiple word works' => [
				'sentence' => 'slovo0 hahaha slovo2 abc',
				'words' => [
					ISentenceScoringService::CATEGORY_3 => [
						[
							'hahaha slovo',
							ISentenceScoringService::MATCHTYPE_SUBSTRING,
						],
					],
					ISentenceScoringService::CATEGORY_2 => [['abc', ISentenceScoringService::MATCHTYPE_SUBSTRING]],
				],
				'resultsScore' => 18,
				'resultStatus' => ISentenceScoringService::HARDCORE_LEVEL_ILLEGAL,
			],
		];
	}


	/**
	 * @param array<string,array<mixed>> $words
	 * @dataProvider dpAnalyzeSentence
	 */
	public function testAnalyzeSentence_callAnalyze_returnsResult(string $sentence, array $words, int $resultScore, string $resultStatus): void
	{
		$presence = [
			ISentenceScoringService::CATEGORY_0 => [],
			ISentenceScoringService::CATEGORY_1 => [],
			ISentenceScoringService::CATEGORY_2 => [],
			ISentenceScoringService::CATEGORY_3 => [],
			ISentenceScoringService::CATEGORY_4 => [],
			ISentenceScoringService::CATEGORY_5 => [],
		];
		foreach ($words as $category => $categoryWords) {
			foreach ($categoryWords as $word) {
				$presence[$category][] = $this->createMock(IFoundWord::class);
			}
		}

		$search = $this->createMock(ISearch::class);
		$search->method('findWordsInCategory')->willReturnCallback(fn ($category) => $presence[$category]);

		$searchFactory = $this->createMock(ISearchFactory::class);
		$searchFactory->method('getSearch')->with($sentence)->willReturn($search);

		$service = new ScoringService($searchFactory);

		$result = $service->analyzeSentence($sentence);

		$this->assertEquals($resultScore, $result->getScore(), 'Wrong sentence score!');
		$this->assertEquals($resultStatus, $result->getHardcoreLevel(), 'Wrong hardcore level!');
	}
}
