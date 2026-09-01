<template x-if="currentQuestion.type === 'speak_repeat' || currentQuestion.type === 'speak-repeat'">
    <div class="speak-layout">
        
        <div class="speak-target-card">
            <!-- Target Card Image (Prompt Image or Option 1 Image) -->
            <template x-if="currentQuestion.image || (currentQuestion.options && currentQuestion.options[0] && currentQuestion.options[0].image)">
                <img :src="currentQuestion.image || currentQuestion.options[0].image" class="speak-image" x-on:error="$el.style.display='none'">
            </template>
            
            <div class="speak-word">
                <span x-text="speakTargetWord || (currentQuestion.options && currentQuestion.options[0] ? currentQuestion.options[0].text : '')" style="text-transform: capitalize;"></span>
                <button @click.prevent="playTargetWord()" class="speak-audio-btn" :class="{ 'playing': isSpeaking }" aria-label="Play Word Audio">🔊</button>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; align-items:center; gap: 10px;">
            <div class="speak-mic-container">
                <button class="speak-mic-btn"
                        :class="{ 'recording': speakListening }"
                        :disabled="speakCompleted"
                        @touchstart.prevent="startSpeakHold()" 
                        @mousedown.prevent="startSpeakHold()"
                        @touchend.prevent="endSpeakHold()" 
                        @mouseup.prevent="endSpeakHold()"
                        @mouseleave.prevent="endSpeakHold()">
                    🎤
                </button>
                <div class="speak-hint" x-show="!speakCompleted">HOLD TO TALK</div>
            </div>

            <div class="speak-status" :class="{ 'recording': speakListening }" x-text="speakStatus"></div>

            <div class="speak-dots">
                <template x-for="(dot, index) in speakDots" :key="index">
                    <div class="speak-dot" :class="{ 'filled': dot }"></div>
                </template>
            </div>
            
            <button class="speak-skip-btn" @click="skipSpeak()" x-show="!speakCompleted">⏭️ Skip</button>
        </div>

    </div>
</template>
