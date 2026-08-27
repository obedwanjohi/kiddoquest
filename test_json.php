<?php
$mission = App\Models\Mission::where('slug', 'final')->first();
$mission->load(['questionBank.questions.options', 'questionBank.questions.quizType']);
$drawnQuestions = $mission->questionBank->drawQuestions(10);
$mission->setRelation('questions', $drawnQuestions);

$questionsJson = $mission->questions->map(function ($q) {
    $rawSlug = $q->quizType ? $q->quizType->slug : 'multiple-choice';
    $typeSlug = str_replace('-', '_', $rawSlug);
    $typeName = $q->quizType ? $q->quizType->name : 'Question';
    $typeIcon = $q->quizType ? ($q->quizType->icon ?? '❓') : '❓';

    $narrationAudioUrl = null;
    if ($q->narration && $q->narration->has_audio) {
        $narrationAudioUrl = $q->narration->audio_url;
    }

    return [
        'id' => $q->id,
        'prompt' => $q->prompt,
        'hint' => $q->hint,
        'explanation' => $q->explanation,
        'image' => $q->prompt_image_url,
        'audio' => $q->prompt_audio_url ?: $narrationAudioUrl,
        'type' => $typeSlug,
        'typeName' => $typeName,
        'typeIcon' => $typeIcon,
        'points' => $q->points,
        'metadata' => $q->metadata,
        'scoring_config' => $q->scoring_config,
        'options' => $q->options->map(function ($opt) {
            return [
                'id' => $opt->id,
                'text' => $opt->text_value,
                'image' => $opt->image_url,
                'audio' => $opt->audio_url,
                'is_correct' => (bool) $opt->is_correct,
                'content_type' => $opt->content_type,
                'match_key' => $opt->match_key,
            ];
        })->values()->toArray(),
    ];
})->values()->toArray();

echo json_encode($questionsJson, JSON_PRETTY_PRINT);
