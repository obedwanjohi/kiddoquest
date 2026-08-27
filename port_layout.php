<?php
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($bladeFile);

// 1. Update CSS block inside @push('kid-styles')
$newCss = <<<CSS
/* NEW PREMIUM STYLES */
:root {
    --kid-bg: #FFF9E6;
    --kid-primary: #FFB347;
    --kid-text: #5A3E36;
    --btn-blue: #60A5FA;
    --btn-blue-dark: #3B82F6;
    --btn-green: #34D399;
    --btn-green-dark: #10B981;
    --btn-red: #F87171;
    --btn-red-dark: #EF4444;
    --btn-yellow: #FCD34D;
    --btn-yellow-dark: #F59E0B;
}

body, html {
    background-color: var(--kid-bg);
    color: var(--kid-text);
    font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    overscroll-behavior: none;
}

.screen-container {
    max-width: 900px;
    margin: 0 auto;
    height: 100svh; 
    display: flex;
    flex-direction: column;
    position: relative;
    background-image: radial-gradient(#FDE68A 10%, transparent 11%), radial-gradient(#FDE68A 10%, transparent 11%);
    background-size: 60px 60px;
    background-position: 0 0, 30px 30px;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    flex-shrink: 0;
    z-index: 10;
    position: relative;
}
.icon-btn {
    font-size: 24px;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    box-shadow: 0 4px 0 rgba(0,0,0,0.05);
    text-decoration: none;
    color: var(--kid-text);
}
.progress-bar {
    flex-grow: 1;
    margin: 0 16px;
    height: 14px;
    background: white;
    border-radius: 20px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    overflow: hidden;
    display: flex;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--btn-green), var(--btn-yellow), var(--btn-red));
    border-radius: 20px;
    transition: width 0.5s ease;
}

.content-wrapper {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-bottom: 20px;
    overflow: hidden; 
}

.lion-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.action-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end; 
    flex-grow: 1;
}

.lion-emoji {
    font-size: 75px;
    line-height: 1;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    margin-bottom: 8px;
    transition: transform 0.3s;
}
.lion-emoji.celebrating {
    transform: scale(1.2) translateY(-10px);
}

.speech-bubble {
    background: white;
    padding: 12px 24px 12px 20px; 
    border-radius: 20px;
    font-size: 22px;
    font-weight: 800;
    color: var(--kid-text);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    position: relative;
    text-align: center;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.speech-bubble::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 0 10px 10px 10px;
    border-style: solid;
    border-color: transparent transparent white transparent;
}

.audio-btn {
    background: var(--btn-yellow);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 0 var(--btn-yellow-dark);
    cursor: pointer;
    border: none;
    padding: 0;
}
.audio-btn:active {
    transform: translateY(4px);
    box-shadow: 0 0px 0 var(--btn-yellow-dark);
}

.main-image {
    max-height: 160px;
    object-fit: contain;
    display: block;
    margin-bottom: 15px;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));
}

.layout-square {
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 0 10px;
    width: 100%;
    box-sizing: border-box;
}

.square-btn {
    flex: 1;
    max-width: 100px;
    aspect-ratio: 1;
    background: white;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 16px;
    box-shadow: 0 6px 0 rgba(0,0,0,0.1);
    border: 3px solid transparent;
    cursor: pointer;
    transition: transform 0.1s;
    user-select: none;
}
.square-btn img {
    max-height: 35px;
    margin-bottom: 4px;
}
.square-btn:active {
    transform: translateY(4px);
    box-shadow: 0 2px 0 rgba(0,0,0,0.1);
}
.square-btn.green { background: var(--btn-green); color: white; border-color: var(--btn-green-dark); box-shadow: 0 6px 0 var(--btn-green-dark); }
.square-btn.blue { background: var(--btn-blue); color: white; border-color: var(--btn-blue-dark); box-shadow: 0 6px 0 var(--btn-blue-dark); }
.square-btn.red { background: var(--btn-red); color: white; border-color: var(--btn-red-dark); box-shadow: 0 6px 0 var(--btn-red-dark); }
.square-btn.yellow { background: var(--btn-yellow); color: white; border-color: var(--btn-yellow-dark); box-shadow: 0 6px 0 var(--btn-yellow-dark); }

/* Feedback states for square buttons */
.square-btn.correct { background: #D1FAE5; border-color: var(--btn-green-dark); color: var(--btn-green-dark); box-shadow: 0 6px 0 var(--btn-green-dark); animation: kid-pop 0.4s; }
.square-btn.incorrect { background: #F3F4F6; border-color: #E5E7EB; color: #9CA3AF; box-shadow: 0 6px 0 #E5E7EB; opacity: 0.7; animation: gentle-shake 0.4s; }

/* Feedback states for vertical cards */
.vertical-card.correct { background: #D1FAE5; border-color: var(--btn-green-dark); animation: kid-pop 0.4s; }
.vertical-card.incorrect { background: #F3F4F6; border-color: #E5E7EB; opacity: 0.7; animation: gentle-shake 0.4s; }


.layout-vertical {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    width: 100%;
    max-width: 400px;
    padding: 0 20px;
    box-sizing: border-box;
}

.vertical-card {
    width: 100%;
    background: white;
    border-radius: 20px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border: 3px solid transparent;
    box-shadow: 0 6px 0 rgba(0,0,0,0.1);
    cursor: pointer;
    user-select: none;
    transition: transform 0.1s;
}
.vertical-card:active {
    transform: translateY(4px);
    box-shadow: 0 2px 0 rgba(0,0,0,0.1);
}
.vertical-card .index-num {
    position: absolute;
    top: 12px;
    left: 16px;
    font-size: 20px;
    font-weight: 900;
}
.vertical-card .card-img {
    max-height: 80px;
    object-fit: contain;
}
.vertical-card.blue { border-color: var(--btn-blue); box-shadow: 0 6px 0 rgba(96, 165, 250, 0.4); }
.vertical-card.blue .index-num { color: var(--btn-blue); }
.vertical-card.green { border-color: var(--btn-green); box-shadow: 0 6px 0 rgba(52, 211, 153, 0.4); }
.vertical-card.green .index-num { color: var(--btn-green); }
.vertical-card.red { border-color: var(--btn-red); box-shadow: 0 6px 0 rgba(248, 113, 113, 0.4); }
.vertical-card.red .index-num { color: var(--btn-red); }
.vertical-card.yellow { border-color: var(--btn-yellow); box-shadow: 0 6px 0 rgba(252, 211, 77, 0.4); }
.vertical-card.yellow .index-num { color: var(--btn-yellow); }

@media (min-width: 500px) and (orientation: landscape) {
    .content-wrapper {
        flex-direction: row;
        align-items: stretch; 
        padding: 10px 20px 20px 20px;
        gap: 20px;
    }
    .lion-column {
        flex: 1;
        justify-content: center;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 30px;
        padding: 20px;
        box-shadow: inset 0 0 20px rgba(255,255,255,0.5);
    }
    .action-column {
        flex: 1.2;
        justify-content: center; 
        background: white; 
        border-radius: 30px;
        padding: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
    }
    .main-image {
        max-height: 120px; 
        margin-bottom: 20px;
    }
    .lion-emoji {
        font-size: 85px;
    }
    .speech-bubble {
        margin-bottom: 0;
    }
    .layout-square {
        gap: 16px;
    }
    .square-btn {
        max-width: 110px;
    }
    .layout-vertical {
        max-height: 250px;
        overflow-y: auto;
    }
    .vertical-card {
        height: 80px;
    }
    .vertical-card .card-img {
        max-height: 60px;
    }
}

@keyframes kid-pop {
    0% { transform: scale(0.9); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
@keyframes gentle-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.idle-hint { display: none !important; }

CSS;

$content = preg_replace('/<style>.*?<\/style>/s', "<style>\n$newCss\n</style>", $content);

// 2. Replace HTML Body Structure
$newBody = <<<HTML
<div class="screen-container" x-data="quizEngine(window.__quizConfig)" x-init="init()" @click="if(window.KidSoundLayer) window.KidSoundLayer.init()">

    <!-- Loading -->
    <div x-show="!initialized" style="display:flex;align-items:center;justify-content:center;height:100%;flex-direction:column;gap:16px;">
        <div style="font-size:64px;animation:kid-wiggle 2s ease-in-out infinite;">🦁</div>
        <p style="font-size:24px;font-weight:900;">Loading Quiz...</p>
    </div>

    <!-- HEADER -->
    <div class="header" x-show="initialized" x-cloak>
        <a href="#" class="icon-btn" @click.prevent="exitQuiz()">🏠</a>
        <div class="progress-bar">
            <div class="progress-bar-fill" :style="\`width: \${progressPercent}%\`"></div>
        </div>
        <a href="#" class="icon-btn">⚙️</a>
    </div>

    <template x-if="!quizComplete">
        <div class="content-wrapper" x-show="initialized" x-cloak>
            
            <div class="lion-column">
                <div class="lion-emoji" :class="{ celebrating: leoCelebrating }">🦁</div>
                <div class="speech-bubble">
                    <button @click="playQuestionAudio()" class="audio-btn" aria-label="Replay Audio">🔊</button>
                    <!-- Show Leo message if exists, else question prompt -->
                    <span x-html="leoMessage ? leoMessage.replace(/\\n/g, '<br>') : currentQuestion.prompt"></span>
                </div>
            </div>

            <div class="action-column">
                <template x-if="currentQuestion.image">
                    <img :src="currentQuestion.image" alt="Question" class="main-image" x-on:error="currentQuestion.image = null">
                </template>

                <!-- MULTIPLE CHOICE LAYOUTS -->
                <template x-if="['multiple_choice','tap_answer','true_false','listen_choose'].includes(currentQuestion.type)">
                    <div style="width:100%; display:flex; justify-content:center;">
                        
                        <!-- SQUARE LAYOUT (If no options have images) -->
                        <template x-if="!currentQuestion.options.some(o => o.image)">
                            <div class="layout-square">
                                <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                    <div class="square-btn"
                                         :class="getCardClass(i, option.is_correct) + ' ' + (i%4===0 ? 'green' : i%4===1 ? 'blue' : i%4===2 ? 'red' : 'yellow')"
                                         @click="selectOption(i)">
                                         
                                         <!-- Guess emoji icon if available in text (dummy fallback) -->
                                        <div style="font-size:28px; margin-bottom:2px;" x-show="option.text.includes('Nature')">☀️</div>
                                        <div style="font-size:28px; margin-bottom:2px;" x-show="option.text.includes('Letters')">🔤</div>
                                        <div style="font-size:28px; margin-bottom:2px;" x-show="option.text.includes('Numbers')">🔢</div>
                                        
                                        <span x-text="option.text.replace('Nature','Nature').replace('Letters','Letters').replace('Numbers','Numbers')"></span>
                                        
                                        <template x-if="answered && selectedIndex === i && option.is_correct">
                                            <span style="position:absolute; top: -10px; right: -10px; font-size: 24px; background:white; border-radius:50%;">✅</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- VERTICAL LAYOUT (If ANY option has an image) -->
                        <template x-if="currentQuestion.options.some(o => o.image)">
                            <div class="layout-vertical">
                                <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                    <div class="vertical-card"
                                         :class="getCardClass(i, option.is_correct) + ' ' + (i%4===0 ? 'green' : i%4===1 ? 'blue' : i%4===2 ? 'red' : 'yellow')"
                                         @click="selectOption(i)">
                                        
                                        <div class="index-num" x-text="i + 1"></div>
                                        
                                        <template x-if="option.image">
                                            <img :src="option.image" class="card-img" x-on:error="option.image = null">
                                        </template>
                                        <template x-if="!option.image && option.text">
                                            <span x-text="option.text" style="font-size:32px; font-weight:800;"></span>
                                        </template>

                                        <template x-if="answered && selectedIndex === i && option.is_correct">
                                            <span style="position:absolute; top: -10px; right: -10px; font-size: 24px; background:white; border-radius:50%;">✅</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                    </div>
                </template>
                
                <!-- OTHER QUESTION TYPES WILL GO HERE LATER -->
                <template x-if="!['multiple_choice','tap_answer','true_false','listen_choose'].includes(currentQuestion.type)">
                    <div style="text-align:center; padding: 20px;">
                        <h3>Type: <span x-text="currentQuestion.type"></span></h3>
                        <p>Layout not ported yet. Please test multiple_choice.</p>
                    </div>
                </template>

            </div>
        </div>
    </template>

    {{-- COMPLETION SCREEN --}}
    <template x-if="quizComplete">
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap: 20px;">
            <div style="font-size:80px;">🎉</div>
            <h2 style="font-size:32px; font-weight:900;">Mission Complete!</h2>
            <div style="font-size:24px;">You earned <span x-text="starsEarned" style="color:var(--btn-yellow-dark);font-weight:bold;"></span> stars!</div>
            <button @click="finishQuiz()" style="margin-top:20px; padding:16px 32px; font-size:20px; font-weight:800; background:var(--btn-green); color:white; border:none; border-radius:20px; box-shadow:0 6px 0 var(--btn-green-dark);">Continue</button>
        </div>
    </template>
</div>
HTML;

$content = preg_replace('/<div class="quiz-stage".*?<\/div>(\s*@endsection)/s', "$newBody$1", $content);

file_put_contents($bladeFile, $content);
echo "engine.blade.php updated.\n";
