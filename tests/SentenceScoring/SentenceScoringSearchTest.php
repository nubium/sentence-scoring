<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Tests\SentenceScoring;

use PHPUnit\Framework\TestCase;
use Nubium\SentenceScoring\Edge\Bridge\IPrepareKeywords;
use Nubium\SentenceScoring\Edge\ISentenceScoringService;
use Nubium\SentenceScoring\Internal\Dictionary\IDictionary;
use Nubium\SentenceScoring\Edge\Dictionary\Word;
use Nubium\SentenceScoring\Internal\Search\Search;

class SentenceScoringSearchTest extends TestCase
{
	/**
	 * @return array<string,array<mixed>>
	 */
	public static function dpAnalyzeSentence(): array
	{
		return [
			'fullmatch works 1' => [
				'sentence' => ' slovo0 slovo1 slovo2 ',
				'words' => [
					['slovo1', ISentenceScoringService::CATEGORY_1, ISentenceScoringService::MATCHTYPE_FULLMATCH],
				],
				'resultWords' => [
					ISentenceScoringService::CATEGORY_1 => ['slovo1'],
				],
			],
			'fullmatch works 2' => [
				'sentence' => ' slovo0 slovo1screwmatch slovo2 ',
				'words' => [
					['slovo1', ISentenceScoringService::CATEGORY_1, ISentenceScoringService::MATCHTYPE_FULLMATCH],
				],
				'resultFoundWords' => [],
			],
			'partialmatch works 1' => [
				'sentence' => ' slovo0 slovo1screwmatch slovo2 ',
				'words' => [
					['slovo1', ISentenceScoringService::CATEGORY_1, ISentenceScoringService::MATCHTYPE_SUBSTRING],
				],
				'resultFoundWords' => [
					ISentenceScoringService::CATEGORY_1 => ['slovo1'],
				],
			],
			'C3 removes C1' => [
				'sentence' => ' slovo0 hahaha slovo2 ',
				'words' => [
					['haha', ISentenceScoringService::CATEGORY_3, ISentenceScoringService::MATCHTYPE_SUBSTRING],
					['ha', ISentenceScoringService::CATEGORY_1, ISentenceScoringService::MATCHTYPE_SUBSTRING],
				],
				'resultWords' => [
					ISentenceScoringService::CATEGORY_3 => ['haha'],
				],
			],
			'multiple word works' => [
				'sentence' => ' slovo0 hahaha slovo2 abc ',
				'words' => [
					['hahaha slovo', ISentenceScoringService::CATEGORY_3, ISentenceScoringService::MATCHTYPE_SUBSTRING],
					['abc', ISentenceScoringService::CATEGORY_2, ISentenceScoringService::MATCHTYPE_SUBSTRING],
				],
				'resultWords' => [
					ISentenceScoringService::CATEGORY_2 => ['abc'],
					ISentenceScoringService::CATEGORY_3 => ['hahaha slovo'],
				],
			],
		];
	}


	/**
	 * @param array<mixed> $words
	 * @return array<string,array<mixed>>
	 */
	protected function prepareWords(array $words): array
	{
		$result = [
			'byCat' => [
				ISentenceScoringService::CATEGORY_0 => [],
				ISentenceScoringService::CATEGORY_1 => [],
				ISentenceScoringService::CATEGORY_2 => [],
				ISentenceScoringService::CATEGORY_3 => [],
				ISentenceScoringService::CATEGORY_4 => [],
				ISentenceScoringService::CATEGORY_5 => [],
			],
			'single' => [],
			'boundaries' => [],
			'substrings' => [],
		];
		foreach ($words as $row) {
			$word = new Word($row[0], $row[1], $row[2]);

			$result['byCat'][$word->getCategory()][$word->getWord()] = $word;

			$parts = explode(' ', $word->getWord());
			$lastPart = null;
			foreach ($parts as $part) {
				// single words to match
				$result['single'][$part][] = $word;

				// word boundaries to match
				if (!is_null($lastPart)) {
					// posledne pismeno slova + prve pismeno dalsieho
					$result['boundaries'][substr($lastPart, -1) . ' ' . substr($part, 0, 1)][] = $word;
				}
				$lastPart = $part;
			}
			if ($word->getMatchType() == ISentenceScoringService::MATCHTYPE_SUBSTRING
				&& count($parts) == 1
			) {
				$result['substrings'][] = $word;
			}
		}

		return $result;
	}


	/**
	 * @param array<mixed> $words
	 * @param array<mixed> $resultWords
	 *
	 * @dataProvider dpAnalyzeSentence
	 */
	public function testAnalyzeSentence_callAnalyze_returnsResult(string $sentence, array $words, array $resultWords): void
	{
		$words = $this->prepareWords($words);

		$dictionary = $this->createMock(IDictionary::class);
		$dictionary->method('getSubstringWords')->willReturn($words['substrings']);
		$dictionary->method('getSingleWords')->willReturn($words['single']);
		$dictionary->method('getWordBoundaries')->willReturn($words['boundaries']);
		$dictionary->method('getWordsByCategory')->willReturnCallback(fn ($category) => $words['byCat'][$category]);

		$keywordPrepare = $this->createMock(IPrepareKeywords::class);
		$keywordPrepare->method('stripToKeywords')->with($sentence)->willReturn($sentence);

		$search = new Search($dictionary, $keywordPrepare, $sentence);

		foreach ($resultWords as $category => $resultCategoryWords) {
			$presenceList = $search->findWordsInCategory($category);
			$this->assertCount(
				count($resultCategoryWords), $presenceList,
				'Wrong found words count of category \'' . $category . '\'!'
			);
			foreach ($presenceList as $presence) {
				$word = $presence->getWord();
				$this->assertContains(
					$word,
					$resultCategoryWords,
					'Wrong found word \'' . $word . '\' of category \'' . $category . '\'!'
				);
			}
		}

		if (empty($resultWords)) {
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_0));
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_1));
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_2));
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_3));
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_4));
			$this->assertSame([], $search->findWordsInCategory(ISentenceScoringService::CATEGORY_5));
		}
	}
}
