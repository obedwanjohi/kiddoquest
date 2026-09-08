<?php

namespace App\Services\Learning;

/**
 * What the server decided a mission attempt was worth. The client's own numbers
 * are only used for instant feedback; these are the ones that get stored.
 */
class ScoreResult
{
    /**
     * @param  array<int,bool>  $perQuestion  question id => correct
     * @param  array<int,string>  $notes  question id => why it was skipped or adjusted
     */
    public function __construct(
        public readonly int $score,
        public readonly int $total,
        public readonly int $stars,
        public readonly bool $passed,
        public readonly int $percentage,
        public readonly array $perQuestion = [],
        public readonly array $notes = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'score'      => $this->score,
            'total'      => $this->total,
            'stars'      => $this->stars,
            'passed'     => $this->passed,
            'percentage' => $this->percentage,
        ];
    }

    /** True when the client claimed something different from what we computed. */
    public function disagreesWith(?int $claimedScore, ?int $claimedStars): bool
    {
        return ($claimedScore !== null && $claimedScore !== $this->score)
            || ($claimedStars !== null && $claimedStars !== $this->stars);
    }
}
