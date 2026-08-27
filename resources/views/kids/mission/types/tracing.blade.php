<template x-if="currentQuestion.type === 'tracing'">
    <div class="tracing-layout">
        
        <div class="tracing-board">
            <!-- Canvas is bound to Alpine initTracingCanvas -->
            <canvas x-ref="tracingCanvas" 
                    x-init="initTracingCanvas($refs.tracingCanvas)" 
                    :style="'width: 100%; aspect-ratio: 1; touch-action: none; border-radius: 20px;' + (currentQuestion.image ? ' background-image: url(' + currentQuestion.image + '); background-size: contain; background-position: center; background-repeat: no-repeat;' : '')">
            </canvas>
            
            <div class="tracing-controls-container">
                <div class="tracing-controls">
                    <button class="tracing-btn secondary" @click="playTracingDemo()" :disabled="tracingDemoPlaying">👀 Watch</button>
                    <button class="tracing-btn secondary" @click="clearTracing()">🧹 Clear</button>
                </div>
                
                <!-- Moved inside container, status text removed -->
                <button class="tracing-btn primary" @click="doneTracing()" :disabled="(tracingStrokes === 0 && !tracingDemoPlaying) || tracingCompleted" x-show="!tracingCompleted">I'm Done! ✨</button>
            </div>
        </div>

    </div>
</template>
