<template x-if="currentQuestion.type === 'speak_repeat' || currentQuestion.type === 'speak-repeat'">
    <div class="speak-layout">
        
        <div class="speak-target-card">
            <!-- Global action-column image rendered manually here to better control layout -->
            <template x-if="currentQuestion.image">
                <img :src="currentQuestion.image" class="speak-image">
            </template>
            
            <div class="speak-word">
                <span x-text="speakTargetWord"></span>
                <button @click.prevent="playTargetWord()" class="speak-audio-btn" :class="{ 'playing': isSpeaking }">🔊</button>
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
