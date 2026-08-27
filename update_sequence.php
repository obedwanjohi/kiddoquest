<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

// UPDATE 1: sequence-slot (when slot !== null)
$oldSlotNumber = '<span class="slot-number" x-text="seqCards[slot].text"></span>';
$newSlotNumber = <<<HTML
<div class="slot-number" style="display:flex; justify-content:center; align-items:center; width:100%; height:100%;">
                                            <template x-if="seqCards[slot].image">
                                                <img :src="seqCards[slot].image" alt="" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">
                                            </template>
                                            <template x-if="!seqCards[slot].image && seqCards[slot].text && seqCards[slot].text.trim() !== ''">
                                                <span x-text="seqCards[slot].text"></span>
                                            </template>
                                        </div>
HTML;
$content = str_replace($oldSlotNumber, $newSlotNumber, $content);

// UPDATE 2: sequence-card (in the tray)
$oldSeqCardText = '<span x-text="card.text"></span>';
// Wait, I must be careful to only replace the ONE inside sequence-card.
// I will use regex or just str_replace if it's unique enough.
// Actually, let's just do a string replace on a larger block to be safe.

$oldTrayBlock = <<<HTML
                        <div class="sequence-tray">
                            <template x-for="(card, i) in seqCards" :key="'card-' + i">
                                <div class="sequence-card"
                                     :class="getSeqCardClass(i)"
                                     @click="selectSeqCard(i)">
                                    <span x-text="card.text"></span>
                                </div>
                            </template>
                        </div>
HTML;

$newTrayBlock = <<<HTML
                        <div class="sequence-tray">
                            <template x-for="(card, i) in seqCards" :key="'card-' + i">
                                <div class="sequence-card"
                                     :class="getSeqCardClass(i)"
                                     @click="selectSeqCard(i)"
                                     style="display:flex; justify-content:center; align-items:center;">
                                    <template x-if="card.image">
                                        <img :src="card.image" alt="" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">
                                    </template>
                                    <template x-if="!card.image && card.text && card.text.trim() !== ''">
                                        <span x-text="card.text"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
HTML;

$content = str_replace($oldTrayBlock, $newTrayBlock, $content);

file_put_contents($file, $content);
echo "Updated sequence drag logic to support images.\n";
