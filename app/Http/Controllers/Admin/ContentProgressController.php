<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mission;
use App\Models\Lesson;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;

class ContentProgressController extends Controller
{
    public function index(Request $request)
    {
        $missions = Mission::with(['lesson', 'adventureWorld', 'questionBank.questions.options', 'questionBank.questions.quizType'])->get();

        $stats = [
            'total_lessons' => Lesson::count(),
            'total_missions' => $missions->count(),
            'total_banks' => QuestionBank::count(),
            'total_questions' => 0,
            'target_questions' => 0,
            'total_videos' => 0,
            'target_videos' => $missions->count(),
            'images_uploaded' => 0,
            'images_required' => 0,
            'audio_uploaded' => 0,
            'audio_required' => 0,
            'ready_missions' => 0,
        ];

        $missionData = [];

        foreach ($missions as $m) {
            $row = [
                'id' => $m->id,
                'thumbnail' => $m->thumbnail_url ?? null, // Use accessor if exists, or null
                'title' => $m->display_title ?? $m->title,
                'lesson' => $m->lesson ? $m->lesson->title : 'Unknown',
                'lesson_id' => $m->lesson_id,
                'world' => $m->adventureWorld ? $m->adventureWorld->name : 'Unknown',
                'video_uploaded' => !empty($m->video_url),
                'bank_name' => $m->questionBank ? $m->questionBank->name : 'Missing',
                'bank_id' => $m->question_bank_id,
                'questions_current' => 0,
                'questions_target' => $m->questions_per_session ?? 5,
                'images_uploaded' => 0,
                'images_required' => 0,
                'missing_images' => [],
                'audio_uploaded' => 0,
                'audio_required' => 0,
                'status' => '🔴 Incomplete',
                'completion_percent' => 0,
            ];

            if ($row['video_uploaded']) {
                $stats['total_videos']++;
            }

            $stats['target_questions'] += $row['questions_target'];

            if ($m->questionBank) {
                $questions = $m->questionBank->questions;
                $row['questions_current'] = $questions->count();
                $stats['total_questions'] += $row['questions_current'];

                foreach ($questions as $q) {
                    $code = $q->quizType ? $q->quizType->code : null;

                    // Images Logic
                    // 1. Question prompts that require images
                    if (in_array($code, ['QT-09', 'QT-13'])) {
                        $row['images_required']++;
                        $stats['images_required']++;
                        if (!empty($q->prompt_image_url)) {
                            $row['images_uploaded']++;
                            $stats['images_uploaded']++;
                        } else {
                            $row['missing_images'][] = "Question ID {$q->id} missing prompt image";
                        }
                    }

                    // 2. Options that require images
                    foreach ($q->options as $opt) {
                        if ($opt->content_type === 'image') {
                            $row['images_required']++;
                            $stats['images_required']++;
                            if (!empty($opt->image_url)) {
                                $row['images_uploaded']++;
                                $stats['images_uploaded']++;
                            } else {
                                $row['missing_images'][] = "Option ID {$opt->id} missing image";
                            }
                        }
                    }

                    // Audio Logic
                    if (in_array($code, ['QT-06', 'QT-07'])) {
                        $row['audio_required']++;
                        $stats['audio_required']++;
                        if (!empty($q->prompt_audio_url) || !empty($q->narration_text)) {
                            $row['audio_uploaded']++;
                            $stats['audio_uploaded']++;
                        }
                    }
                }
            }

            // Calculate Completion Percentage
            $totalReqs = 1; // Video is always a requirement
            $completedReqs = $row['video_uploaded'] ? 1 : 0;

            // Question Count constraint
            $totalReqs += $row['questions_target'];
            $completedReqs += min($row['questions_current'], $row['questions_target']);

            // Media
            if ($row['images_required'] > 0) {
                $totalReqs += $row['images_required'];
                $completedReqs += $row['images_uploaded'];
            }
            if ($row['audio_required'] > 0) {
                $totalReqs += $row['audio_required'];
                $completedReqs += $row['audio_uploaded'];
            }

            $percent = $totalReqs > 0 ? round(($completedReqs / $totalReqs) * 100) : 0;
            $row['completion_percent'] = $percent;

            if ($percent >= 100 && $row['questions_current'] >= $row['questions_target']) {
                $row['status'] = '🟢 Ready';
                $stats['ready_missions']++;
            } elseif ($percent >= 75) {
                $row['status'] = '🟡 In Progress';
            } else {
                $row['status'] = '🔴 Incomplete';
            }

            $missionData[] = $row;
        }

        // Apply filters if any
        if ($request->has('status') && $request->status != '') {
            $statusStr = $request->status == 'ready' ? '🟢 Ready' : ($request->status == 'progress' ? '🟡 In Progress' : '🔴 Incomplete');
            $missionData = array_filter($missionData, function($m) use ($statusStr) {
                return $m['status'] === $statusStr;
            });
        }
        if ($request->has('search') && $request->search != '') {
            $s = strtolower($request->search);
            $missionData = array_filter($missionData, function($m) use ($s) {
                return str_contains(strtolower($m['title']), $s) || 
                       str_contains(strtolower($m['lesson']), $s) ||
                       str_contains(strtolower($m['bank_name']), $s);
            });
        }

        // Group by Adventure World -> Lesson
        $grouped = collect($missionData)->groupBy('world')->map(function($worldGroup) {
            return collect($worldGroup)->groupBy('lesson');
        });

        return view('admin.content-progress.index', compact('grouped', 'stats'));
    }
}
