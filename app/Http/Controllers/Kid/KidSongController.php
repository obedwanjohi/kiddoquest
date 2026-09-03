<?php

namespace App\Http\Controllers\Kid;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;

class KidSongController extends Controller
{
    /**
     * Display Kids Music & Video Songs Hub (Safe Zone after learning time limit).
     */
    public function index()
    {
        $childId = session('active_child_id');
        $child = $childId ? Child::find($childId) : Child::first();

        if (!$child) {
            $child = new Child(['name' => 'Hero', 'total_stars' => 10, 'star_coins' => 50]);
        }

        $songs = [
            [
                'id' => 1,
                'title' => 'The Alphabet Song 🔤',
                'category' => 'Phonics & ABCs',
                'video_url' => 'https://www.youtube.com/embed/ezmsrB59mj8',
                'emoji' => '🔤',
                'bg' => 'from-purple-500 to-indigo-600',
            ],
            [
                'id' => 2,
                'title' => 'Counting 1 to 10 Numbers Song 🔢',
                'category' => 'Mathematics',
                'video_url' => 'https://www.youtube.com/embed/D0Ajq682yrA',
                'emoji' => '🔢',
                'bg' => 'from-amber-400 to-orange-500',
            ],
            [
                'id' => 3,
                'title' => 'Jesus Loves the Little Children ✝️',
                'category' => 'Moral Values & Praise',
                'video_url' => 'https://www.youtube.com/embed/8oP5nS2D-rM',
                'emoji' => '✝️',
                'bg' => 'from-emerald-500 to-teal-600',
            ],
            [
                'id' => 4,
                'title' => 'The Animals Safari Song 🦁',
                'category' => 'Nature & Science',
                'video_url' => 'https://www.youtube.com/embed/pWepfJ-8XU0',
                'emoji' => '🦁',
                'bg' => 'from-sky-400 to-blue-600',
            ],
        ];

        return view('kids.songs', compact('child', 'songs'));
    }
}
