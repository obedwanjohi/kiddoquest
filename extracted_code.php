<div>
            {{-- LEO + QUESTION (side-by-side in landscape) --}}
            <div class="quiz-landscape-split">
                {{-- LEO ZONE --}}
                <div class="leo-zone" x-show="leoMessage">
                    <div class="leo-mascot" :class="{ celebrating: leoCelebrating }">🦁</div>
                    <div class="leo-bubble" x-text="leoMessage"></div>
                </div>

                {{-- QUESTION --}}
                <div class="question-zone" :key="'q-' + currentIndex">
                <div class="question-badge" x-text="`${currentQuestion.typeIcon} ${currentQuestion.typeName}`"></div>
                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <button @click="playQuestionAudio()" 
                            class="kid-btn" 
                            style="width: 48px; height: 48px; border-radius: 50%; background: var(--kid-primary); color: white; border: none; cursor: poin
                                    </template>
                                    <template x-if="option.text && option.text.trim() !== ''">
                                        <span x-text="option.text"></span>
                                    </template>
                                    <template x-if="answered && selectedIndex === i && option.is_correct">
                                        <span class="badge">✅</span>
                                    </template>
                                </div>
                            </template>
