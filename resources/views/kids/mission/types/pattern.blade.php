<template x-if="currentQuestion.type === 'pattern'">
    <div class="pattern-layout">
        
        <!-- Pattern Display Card -->
        <div class="pattern-display-card">
            <template x-if="currentQuestion.image">
                <img :src="currentQuestion.image" class="pattern-main-img" x-on:error="currentQuestion.image = null">
            </template>
            <!-- Show the missing element box (?) or the correct answer if answered -->
            <div class="pattern-question-mark" :style="answered && !getCorrectAnswerImage() ? 'color:#58cc02; font-size: 80px; transition:all 0.3s;' : ''">
                <template x-if="answered && getCorrectAnswerImage()">
                    <img :src="getCorrectAnswerImage()" style="width: 100px; height: 100px; object-fit: contain; animation: popIn 0.3s ease-out forwards; display:block; margin: 0 auto;">
                </template>
                <template x-if="!answered || !getCorrectAnswerImage()">
                    <span x-text="answered ? getCorrectAnswerText() : '?'"></span>
                </template>
            </div>
        </div>

        <!-- Options Row -->
        <div class="pattern-options-row">
            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                <div class="pattern-option-btn"
                     :class="getCardClass(i, option.is_correct) + ' color-' + (i % 4)"
                     @click="selectOption(i)">
                    
                    <template x-if="option.image">
                        <img :src="option.image" x-on:error="option.image = null">
                    </template>
                    <template x-if="!option.image && option.text">
                        <span x-text="option.text"></span>
                    </template>
                    
                </div>
            </template>
        </div>

    </div>
</template>
