<template x-if="currentQuestion.type === 'drag_sort'">
    <div class="drag-sort-container">
        
        <!-- TRAY (Unsorted items - Show ONLY 1 Target Item at a time for Playgroup!) -->
        <div class="sort-tray">
            <template x-for="(chip, i) in sortChips" :key="i">
                <template x-if="chip.bucket === null && i === sortChips.findIndex(c => c.bucket === null)">
                    <div class="sort-chip selected active-target-chip"
                         :class="getSortChipClass(i)"
                         @click="selectSortChip(i)">
                        
                        <template x-if="chip.image">
                            <img :src="chip.image" style="max-width:80%; max-height:80%; object-fit:contain;">
                        </template>
                        <template x-if="!chip.image && chip.text">
                            <span x-text="chip.text"></span>
                        </template>

                    </div>
                </template>
            </template>
        </div>

        <!-- BUCKETS -->
        <div class="sort-buckets-row">
            <template x-for="(catName, bIdx) in sortCategories" :key="bIdx">
                <div class="sort-bucket"
                     :class="getSortBucketClass(bIdx)"
                     @click="selectSortBucket(bIdx)">
                    
                    <div class="bucket-title" x-text="catName"></div>
                    
                    <div class="bucket-items">
                        <template x-for="(chip, i) in sortChips" :key="'chip-'+i">
                            <template x-if="chip.bucket === catName">
                                <div class="bucket-item"
                                     :class="getSortChipInBucketClass(i)"
                                     @click.stop="selectSortChip(i)">
                                    <template x-if="chip.image">
                                        <img :src="chip.image" style="max-width:80%; max-height:80%; object-fit:contain;">
                                    </template>
                                    <template x-if="!chip.image && chip.text">
                                        <span x-text="chip.text"></span>
                                    </template>
                                </div>
                            </template>
                        </template>
                    </div>

                </div>
            </template>
        </div>

    </div>
</template>
