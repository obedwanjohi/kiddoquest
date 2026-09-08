<?php

namespace App\Services\Content;

use App\Services\DevotionalRegistry;

/**
 * The two small pieces of content that are not missions: the daily devotional
 * and the songs hub.
 *
 * Both travel to the app in one small payload and are cached there, because
 * both are things a family reaches for exactly when the connection is worst —
 * a devotional at bedtime, a song in a car. The app picks today's devotional
 * itself from the list rather than asking, so it works with the plane on.
 *
 * The songs carry an `audio` key that is null today. Whether KiddoQuest
 * licenses its own recordings is decision 5 in the plan; until that is
 * answered a song is a link, and the app says plainly that a link needs the
 * internet. When recordings exist, filling in `audio` is the only change
 * needed here and the app will play them offline.
 */
class ExtrasRegistry
{
    /** @return array<int,array> */
    public static function devotionals(): array
    {
        return DevotionalRegistry::all();
    }

    /**
     * The songs hub.
     *
     * @return array<int,array>
     */
    public static function songs(): array
    {
        return [
            [
                'id'       => 1,
                'title'    => 'The Alphabet Song',
                'category' => 'Phonics & ABCs',
                'emoji'    => '🔤',
                'palette'  => 'castle',
                'audio'    => null,
                'link'     => 'https://www.youtube.com/watch?v=ezmsrB59mj8',
            ],
            [
                'id'       => 2,
                'title'    => 'Counting 1 to 10',
                'category' => 'Mathematics',
                'emoji'    => '🔢',
                'palette'  => 'safari',
                'audio'    => null,
                'link'     => 'https://www.youtube.com/watch?v=D0Ajq682yrA',
            ],
            [
                'id'       => 3,
                'title'    => 'Jesus Loves the Little Children',
                'category' => 'Moral Values & Praise',
                'emoji'    => '✝️',
                'palette'  => 'forest',
                'audio'    => null,
                'link'     => 'https://www.youtube.com/watch?v=8oP5nS2D-rM',
            ],
            [
                'id'       => 4,
                'title'    => 'The Animals Safari Song',
                'category' => 'Nature & Science',
                'emoji'    => '🦁',
                'palette'  => 'ocean',
                'audio'    => null,
                'link'     => 'https://www.youtube.com/watch?v=pWepfJ-8XU0',
            ],
        ];
    }
}
