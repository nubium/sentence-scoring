<?php
declare(strict_types=1);

namespace Nubium\SentenceScoring\Edge\Bridge;

interface IPrepareKeywords
{
	/**
	 * Prevede/odstrani nevalidni ascii znaky.
	 */
	public function stripToKeywords(string $sentence): string;
}
