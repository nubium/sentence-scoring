<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge;

interface ISentenceScoringService
{
	const
		HARDCORE_LEVEL_SAFE = 'safe',
		HARDCORE_LEVEL_PORN = 'porn',
		HARDCORE_LEVEL_ILLEGAL = 'illegal';

	const
		// minimal score for PORN
		PORN_LEVEL = 7,
		// maximal score
		BAN_LEVEL = 12;

	const
		CATEGORY_0 = 'C0',
		CATEGORY_1 = 'C1',
		CATEGORY_2 = 'C2',
		CATEGORY_3 = 'C3',
		CATEGORY_4 = 'C4',
		CATEGORY_5 = 'C5';

	const
		MATCHTYPE_SUBSTRING = 1,
		MATCHTYPE_FULLMATCH = 2;


	public function analyzeSentence(string $sentence): IScoringResult;

	public function getVersion(): int;
}
