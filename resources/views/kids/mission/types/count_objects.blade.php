<template x-if="currentQuestion.type === 'count_objects' || currentQuestion.type === 'count-objects'">
    <div class="count-objects-layout">
        
        <!-- Interactive Count Box -->
        <div class="count-display-card">
            <template x-for="(item, i) in countObjectsTapped" :key="'item-'+i">
                <div class="count-item" 
                     :class="countObjectsTapped[i] ? 'tapped' : ''"
                     @click="
                        if(!answered) {
                            countObjectsTapped[i] = !countObjectsTapped[i];
                            if(window.KidSoundLayer) window.KidSoundLayer.playPop();
                        }
                     ">
                    <template x-if="currentQuestion.scoring_config?.emoji">
                        <span x-text="currentQuestion.scoring_config.emoji"></span>
                    </template>
                    <template x-if="currentQuestion.scoring_config?.image_url">
                        <img :src="currentQuestion.scoring_config.image_url" style="max-width: 60px; max-height: 60px; object-fit: contain;">
                    </template>
                </div>
            </template>
        </div>

        <!-- Options Row -->
        <div class="count-options-row">
            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                <div class="count-option-btn"
                     :class="'color-' + (i % 4) + (selectedIndex === i ? ' selected' : '') + (answered && selectedIndex === i && option.is_correct ? ' correct' : '') + (answered && selectedIndex === i && !option.is_correct ? ' incorrect' : '')"
                     @click="selectOption(i)">
                    <span x-text="option.text_value || option.text"></span>
                </div>
            </template>
        </div>

    </div>
</template>
