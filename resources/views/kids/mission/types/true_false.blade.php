<template x-if="currentQuestion.type === 'true_false'">
    <div class="tf-container" :class="{ 'answered': answered }">
        <template x-for="(option, i) in currentQuestion.options" :key="option.id">
            <div class="tf-btn"
                 :class="{
                     'true-btn': option.text.toLowerCase() === 'true',
                     'false-btn': option.text.toLowerCase() === 'false',
                     'selected': selectedIndex === i,
                     'correct': answered && selectedIndex === i && option.is_correct,
                     'incorrect': answered && selectedIndex === i && !option.is_correct
                 }"
                 @click="selectOption(i)">
                
                <div class="tf-btn-icon" x-text="option.text.toLowerCase() === 'true' ? '✅' : '❌'"></div>
                <div class="tf-btn-text" x-text="option.text"></div>
                
            </div>
        </template>
    </div>
</template>
