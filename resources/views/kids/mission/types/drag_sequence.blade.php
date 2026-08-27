<template x-if="currentQuestion.type === 'drag_sequence'">
    <div class="drag-seq-container">
        
        <!-- DROP SLOTS -->
        <div class="seq-slots-row">
            <template x-for="(slotItemIdx, i) in seqSlots" :key="i">
                <div class="seq-slot"
                     :class="getSeqSlotClass(i) + ' ' + (slotItemIdx !== null ? ('outline-' + (slotItemIdx%4===0 ? 'green' : slotItemIdx%4===1 ? 'blue' : slotItemIdx%4===2 ? 'red' : 'yellow')) : '')"
                     @click="selectSeqSlot(i)">
                    
                    <template x-if="slotItemIdx !== null">
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                            <template x-if="seqCards[slotItemIdx].image">
                                <img :src="seqCards[slotItemIdx].image" class="seq-item-img">
                            </template>
                            <template x-if="!seqCards[slotItemIdx].image && seqCards[slotItemIdx].text">
                                <span x-text="seqCards[slotItemIdx].text" class="seq-item-content"></span>
                            </template>
                        </div>
                    </template>

                </div>
            </template>
        </div>

        <!-- TRAY -->
        <div class="seq-tray">
            <template x-for="(card, i) in seqCards" :key="i">
                <div class="seq-tray-item"
                     :class="getSeqCardClass(i) + ' outline-' + (i%4===0 ? 'green' : i%4===1 ? 'blue' : i%4===2 ? 'red' : 'yellow')"
                     @click="selectSeqCard(i)">
                    
                    <template x-if="card.image">
                        <img :src="card.image" class="seq-item-img">
                    </template>
                    <template x-if="!card.image && card.text">
                        <span x-text="card.text" class="seq-item-content"></span>
                    </template>

                </div>
            </template>
        </div>

        <!-- SUBMIT BUTTON (Only show when all slots are filled) -->
        <template x-if="!seqSlots.includes(null) && !seqAnswered">
            <button @click="checkSequence()" class="check-btn" style="width: 100%; max-width: 400px; padding: 15px; font-size: 24px; font-weight: 900; color: white; background: #58cc02; border: none; border-radius: 20px; box-shadow: 0 6px 0 #58a700; cursor: pointer; transition: transform 0.1s;">
                CHECK
            </button>
        </template>

    </div>
</template>
