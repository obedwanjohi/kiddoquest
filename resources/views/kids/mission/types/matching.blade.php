<template x-if="currentQuestion.type === 'matching'">
    <div class="matching-container">
        
        <!-- LEFT COLUMN -->
        <div class="match-column">
            <template x-for="(item, i) in matchLeftItems" :key="'L'+i">
                <div class="match-card"
                     :class="{
                         'selected': matchSelectedSide === 'left' && matchSelectedIndex === i,
                         'matched': matchedPairs.some(p => p.leftIndex === i),
                         'wrong': matchWrongPair && matchWrongPair.leftIndex === i
                     }"
                     @click="selectMatch('left', i)">
                     
                     <template x-if="item.image">
                         <img :src="item.image" style="max-height:80%; max-width:80%; object-fit:contain;">
                     </template>
                     <template x-if="!item.image && item.text">
                         <span x-text="item.text"></span>
                     </template>

                </div>
            </template>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="match-column">
            <template x-for="(item, i) in matchRightItems" :key="'R'+i">
                <div class="match-card"
                     :class="{
                         'selected': matchSelectedSide === 'right' && matchSelectedIndex === i,
                         'matched': matchedPairs.some(p => p.rightIndex === i),
                         'wrong': matchWrongPair && matchWrongPair.rightIndex === i
                     }"
                     @click="selectMatch('right', i)">
                     
                     <template x-if="item.image">
                         <img :src="item.image" style="max-height:80%; max-width:80%; object-fit:contain;">
                     </template>
                     <template x-if="!item.image && item.text">
                         <span x-text="item.text"></span>
                     </template>

                </div>
            </template>
        </div>

    </div>
</template>
