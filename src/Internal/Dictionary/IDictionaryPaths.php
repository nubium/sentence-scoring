<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Internal\Dictionary;

interface IDictionaryPaths
{
	public function getCsvFilePath(int $version): string;

	public function getGeneratedFilePath(): string;
}
