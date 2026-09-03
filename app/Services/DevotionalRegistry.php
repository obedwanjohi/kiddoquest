<?php

namespace App\Services;

class DevotionalRegistry
{
    /**
     * Curated list of child-friendly daily Bible verses, teachings, and 1-sentence prayers.
     */
    public static function getTodayDevotional(): array
    {
        $devotionals = [
            [
                'verse_text' => 'I can do all things through Christ who strengthens me.',
                'verse_ref'  => 'Philippians 4:13',
                'teaching'   => 'God gave you a brave, smart heart today! Whenever you try a new word, math puzzle, or letter, Jesus is cheering for you!',
                'prayer'     => 'Thank You God for this happy day. Help me learn with joy, courage, and kindness. Amen!',
                'emoji'      => '🦁',
            ],
            [
                'verse_text' => 'This is the day the Lord has made; let us rejoice and be glad in it.',
                'verse_ref'  => 'Psalm 118:24',
                'teaching'   => 'Today is a special gift from God made just for you to smile, play, and learn wonderful new things!',
                'prayer'     => 'Dear God, thank You for today. Fill my heart with joy and laughter while I learn! Amen!',
                'emoji'      => '☀️',
            ],
            [
                'verse_text' => 'Trust in the Lord with all your heart.',
                'verse_ref'  => 'Proverbs 3:5',
                'teaching'   => 'God knows your name and loves you so much! Trust Him whenever you start something new.',
                'prayer'     => 'Lord Jesus, I give You my heart today. Walk with me as I discover new adventures! Amen!',
                'emoji'      => '❤️',
            ],
            [
                'verse_text' => 'Be kind and compassionate to one another.',
                'verse_ref'  => 'Ephesians 4:32',
                'teaching'   => 'Sharing a smile, helping a friend, and using gentle words makes God happy!',
                'prayer'     => 'Heavenly Father, teach me to be kind to my family and friends today. Amen!',
                'emoji'      => '🤝',
            ],
            [
                'verse_text' => 'The Lord is my shepherd; I shall not want.',
                'verse_ref'  => 'Psalm 23:1',
                'teaching'   => 'Jesus is your gentle shepherd who protects you, guides you, and leads you beside quiet waters.',
                'prayer'     => 'Jesus my Shepherd, thank You for protecting me and giving me everything I need. Amen!',
                'emoji'      => '🐑',
            ],
        ];

        // Pick daily verse based on day of year
        $dayOfYear = (int) date('z');
        $index = $dayOfYear % count($devotionals);

        return $devotionals[$index];
    }
}
