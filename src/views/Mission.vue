<template>
    <div class="book">
        <div class="d-flex justify-content-center my-5 paragraph-list">
            <img src="@/assets/KrishnaSearchMission.png" class="w-40">
            <div class="m-5 w-45 d-flex flex-column align-items-center">
                <p class="b36">Our mission</p>
                <p class="b65">
                    When Śrīla Śrīdhar Mahārāj founded<br />Śrī Chaitanya Sāraswat Maṭh in 1941, he first composed the following verse encapsulating his mission’s ideal:
                </p>
                <p class="b59">
                    śrīmach-chaitanya-sārasvata-maṭha-vara-udgīta-kīrttir-jayaśrīm<br />
                    bibhrat sambhāti gaṅgā-taṭa-nikaṭa-navadvīpa-kolādrirāje<br />
                    yatra śrī-gaura-sāraswata-mata-niratā gaura-gāthā gṛṇanti<br />
                    nityaṁ rūpānuga-śrī-kṛtamati-guru-gaurāṅga-rādhājitāśā
                </p>
            </div>
        </div>
        <div class="m-5 d-flex flex-column align-items-center paragraph-list">
            <p class="b44">
                “In the holy abode of transcendental touchstone Śrī Nabadwīp Dhām—the selfsame Śrī Vṛndāvan, in the land of Koladwīp, whose inner identity is the king of mountains Śrī Govardhan, near the charming shores of the holy river Bhāgīrathī who delivers all the fallen souls, stands that grand and glorious king of all temples—Śrī Chaitanya Sāraswat Maṭh.
            </p>
            <p class="b44">
                In that beautiful retreat, the surrendered devotees whose lives are the message of Śrī Gaura, are eternally absorbed in the loving service of the Divine Master, the Golden Lord Gaurāṅga, and the Loving Lord Śrī Govindasundar with His consort Śrīmatī Gandharvā, accompanied by all Their beloved associates.
            </p>
            <p class="b44">
                With hearts always filled with the good hope to attain the grace of the Lord, following faithfully in the line of Śrī Rūpa and Raghunāth—the storekeepers of the unlimited treasure of pure, sweet love divine—those surrendered souls ever sing the unending glories of the transcendental Name and qualities of the most magnanimous, original Supreme Lord, Śrī Śrī Gaurāṅgasundar.
            </p>
            <p class="b44">
                Indeed the hearts of all beings, moving and still, marvel in the glory of Śrī Chaitanya Sāraswat Maṭh as they take shelter in the soothing, affectionate shade of the victory flag that flies aloft and sings wholeheartedly to the world the ever-expanding renown of Śrī Chaitanya Sāraswat Maṭh; bearing that banner of all transcendental benedictions, Śrī Chaitanya Sāraswat Maṭh ever shines resplendent in all its divine magnificence.”
            </p>
        </div>
        <div class="d-flex flex-column align-items-center paragraph-list">
            <p class="b36">Our Guardians</p>
            <div class="d-flex justify-content-center">
                <div class="d-flex flex-column align-items-center w-20 m-5">
                    <img src="@/assets/KrishnaSearchGuardian1.png" class="w-100">
                    <p class="b59">Śrīla Govinda Mahārāj</p>
                </div>
                <div class="d-flex flex-column align-items-center w-20 m-5">
                    <img src="@/assets/KrishnaSearchGuardian2.png" class="w-100">
                    <p class="b59">Śrīla Śrīdhar Mahārāj</p>
                </div>
                <div class="d-flex flex-column align-items-center w-20 m-5">
                    <img src="@/assets/KrishnaSearchGuardian3.png" class="w-100">
                    <p class="b59">Śrīla Saraswatī Ṭhākur</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {}
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';
p, h3, h1 {
    color: $theme-color-primary !important;
    //font-family: "Times New Roman" !important;
}

p {
    max-width: 800px;
    //font-size: 20px;
}

.paragraph-list {
    height: unset;
}

.b36, .b65 {
    padding-bottom: 0 !important;
}
</style>
