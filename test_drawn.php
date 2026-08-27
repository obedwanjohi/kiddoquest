<?php
$m = App\Models\Mission::where('slug', 'final')->first();
$m->load(['questionBank.questions.options', 'questionBank.questions.quizType']);
$d = $m->questionBank->drawQuestions(10);
$m->setRelation('questions', $d);
echo count($m->questions);
