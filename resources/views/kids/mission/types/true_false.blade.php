<template x-if="currentQuestion.type === 'true_false'">
    <div class="tf-container" :class="{ 'answered': answered }">
        <template x-for="(option, i) in currentQuestion.options" :key="option.id">
            <div class="tf-btn"
                 :class="{
                     'true-btn': ['true', 'yes', '🟢 yes', 'yes (ndiyo)'].some(t => option.text.toLowerCase().includes(t)),
                     'false-btn': ['false', 'no', '🔴 no', 'no (hapana)'].some(t => option.text.toLowerCase().includes(t)),
                     'selected': selectedIndex === i,
                     'correct': answered && selectedIndex === i && option.is_correct,
                     'incorrect': answered && selectedIndex === i && !option.is_correct
                 }"
                 @click="selectOption(i)">
                
                <div class="tf-btn-icon" x-text="['true', 'yes', '🟢 yes'].some(t => option.text.toLowerCase().includes(t)) ? '👍' : '👎'"></div>
                <div class="tf-btn-text" x-text="option.text"></div>
                
            </div>
        </template>
    </div>
</template>
