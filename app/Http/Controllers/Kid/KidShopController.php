<?php

namespace App\Http\Controllers\Kid;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KidShopController extends Controller
{
    public const SHOP_ITEMS = [
        // --- HATS & ACCESSORIES ---
        [
            'id' => 'hat_star',
            'type' => 'hat',
            'name' => 'Star Badge',
            'emoji' => '🌟',
            'cost' => 0,
            'description' => 'A shiny star badge for super starters! (FREE GIFT)',
        ],
        [
            'id' => 'hat_crown',
            'type' => 'hat',
            'name' => 'Royal Crown',
            'emoji' => '👑',
            'cost' => 50,
            'description' => 'Fit for a learning king or queen!',
        ],
        [
            'id' => 'hat_pirate',
            'type' => 'hat',
            'name' => 'Pirate Hat',
            'emoji' => '🏴‍☠️',
            'cost' => 30,
            'description' => 'Ahoy matey! Ready for adventure!',
        ],
        [
            'id' => 'hat_superhero',
            'type' => 'hat',
            'name' => 'Superhero Cape',
            'emoji' => '🦸',
            'cost' => 60,
            'description' => 'Unleash your super smart powers!',
        ],
        [
            'id' => 'hat_sunglasses',
            'type' => 'hat',
            'name' => 'Cool Sunglasses',
            'emoji' => '🕶️',
            'cost' => 40,
            'description' => 'Too cool for school!',
        ],
        [
            'id' => 'hat_astronaut',
            'type' => 'hat',
            'name' => 'Astronaut Helmet',
            'emoji' => '👨‍🚀',
            'cost' => 100,
            'description' => 'Blast off into learning space!',
        ],
        [
            'id' => 'hat_dino',
            'type' => 'hat',
            'name' => 'Dino Hood',
            'emoji' => '🦖',
            'cost' => 80,
            'description' => 'Roar into math and words!',
        ],
        [
            'id' => 'hat_party',
            'type' => 'hat',
            'name' => 'Party Hat',
            'emoji' => '🥳',
            'cost' => 0,
            'description' => 'Celebrate learning every day! (FREE GIFT)',
        ],

        // --- UNLOCK NEW CHARACTERS ---
        [
            'id' => 'char_panda',
            'type' => 'character',
            'avatar_key' => 'panda',
            'name' => 'Pip the Panda',
            'emoji' => '🐼',
            'cost' => 0,
            'description' => 'Playful panda who loves puzzles! (FREE GIFT)',
        ],
        [
            'id' => 'char_unicorn',
            'type' => 'character',
            'avatar_key' => 'unicorn',
            'name' => 'Uma the Unicorn',
            'emoji' => '🦄',
            'cost' => 0,
            'description' => 'Magical unicorn bringing bright ideas! (FREE GIFT)',
        ],
        [
            'id' => 'char_koala',
            'type' => 'character',
            'avatar_key' => 'koala',
            'name' => 'Koko the Koala',
            'emoji' => '🐨',
            'cost' => 15,
            'description' => 'Gentle koala who stays focused!',
        ],
        [
            'id' => 'char_dino',
            'type' => 'character',
            'avatar_key' => 'dino',
            'name' => 'Rex the Dino',
            'emoji' => '🦖',
            'cost' => 20,
            'description' => 'Strong dinosaur ready for big missions!',
        ],
        [
            'id' => 'char_robot',
            'type' => 'character',
            'avatar_key' => 'robot',
            'name' => 'Beep the Robot',
            'emoji' => '🤖',
            'cost' => 25,
            'description' => 'Super smart robot for math speed!',
        ],
        [
            'id' => 'char_dragon',
            'type' => 'character',
            'avatar_key' => 'dragon',
            'name' => 'Ignis the Dragon',
            'emoji' => '🐉',
            'cost' => 30,
            'description' => 'Friendly dragon who breathes inspiration!',
        ],
    ];

    public function index(): View
    {
        $child = $this->activeChild();
        $items = self::SHOP_ITEMS;

        return view('kids.shop', compact('child', 'items'));
    }

    public function purchase(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string',
        ]);

        $child = $this->activeChild();
        $itemId = $validated['item_id'];
        $item = collect(self::SHOP_ITEMS)->firstWhere('id', $itemId);

        if (! $item) {
            return back()->with('error', 'Item not found in shop!');
        }

        if ($child->hasUnlockedItem($itemId)) {
            return back()->with('error', 'You already unlocked this!');
        }

        if (($child->star_coins ?? 0) < $item['cost']) {
            return back()->with('error', 'Not enough Star Coins! Play more missions to earn coins!');
        }

        // Deduct coins & add to unlocked_items array
        $child->star_coins = max(0, ($child->star_coins ?? 0) - $item['cost']);
        $unlocked = $child->unlocked_items ?? [];
        $unlocked[] = $itemId;
        $child->unlocked_items = array_values(array_unique($unlocked));

        // Auto-equip item based on type
        if ($item['type'] === 'hat') {
            $child->equipped_hat = $itemId;
        } elseif ($item['type'] === 'character') {
            $child->avatar = $item['avatar_key'];
        }

        $child->save();

        return back()->with('success', "🎉 Yay! You unlocked {$item['name']}!");
    }

    public function equip(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:hat,character',
            'item_id' => 'nullable|string',
            'avatar_key' => 'nullable|string',
        ]);

        $child = $this->activeChild();

        if ($validated['type'] === 'hat') {
            $itemId = $validated['item_id'];
            if ($itemId && ! $child->hasUnlockedItem($itemId)) {
                return back()->with('error', 'You must unlock this hat first!');
            }
            $child->equipped_hat = $itemId;
            $child->save();
            return back()->with('success', 'Hat updated!');
        }

        if ($validated['type'] === 'character') {
            $itemId = $validated['item_id'];
            $avatarKey = $validated['avatar_key'];
            if ($itemId && ! $child->hasUnlockedItem($itemId)) {
                return back()->with('error', 'You must unlock this character first!');
            }
            if ($avatarKey) {
                $child->avatar = $avatarKey;
                $child->save();
            }
            return back()->with('success', "Active character updated to {$child->avatar_name}!");
        }

        return back();
    }

    protected function activeChild(): Child
    {
        $childId = session('active_child_id');

        if (! $childId) {
            abort(redirect()->route('kids.profiles'));
        }

        $child = Child::find($childId);

        if (! $child) {
            abort(redirect()->route('kids.profiles'));
        }

        $guardian = Auth::guard('guardian')->user();
        if ($guardian && $child->guardian_id !== $guardian->id) {
            abort(redirect()->route('kids.profiles'));
        }

        return $child;
    }
}
