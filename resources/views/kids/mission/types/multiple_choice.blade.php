<!-- MULTIPLE CHOICE LAYOUTS -->
                <template x-if="['multiple_choice','tap_answer','listen_choose'].includes(currentQuestion.type)">
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
                                <div style="height: 8px; width: 100%; flex-shrink: 0;"></div>
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
                                <div style="height: 8px; width: 100%; flex-shrink: 0;"></div>
                            </div>
                        </template>

                    </div>
                </template>
                
                <!-- OTHER QUESTION TYPES WILL GO HERE LATER -->