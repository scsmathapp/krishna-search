<template>
    <div>
        <div class="d-flex flex-column flex-md-row flex-lg-row flex-xl-row justify-content-center my-md-5 my-lg-5 my-xl-5">
            <div class="d-flex justify-content-center align-items-center p-3">
                <Pic :source="require('@/assets/img/KrishnaSearchMission.png')" w="35vw" h="25vw" class="d-none d-md-flex d-lg-flex d-xl-flex"></Pic>
                <Pic :source="require('@/assets/img/KrishnaSearchMission.png')" w="75vw" h="55vw" class="d-flex d-md-none d-lg-none d-xl-none"></Pic>
            </div>
            <div class="w-sm-100 w-md-45 w-lg-45 d-flex flex-column align-items-center">
                <h1 class="ks-font">Our mission</h1>
                <p class="ks-font">
                    Śrīla Śrīdhar Mahārāj founded the mission Śrī Chaitanya Sāraswat Maṭh in 1941 as a small, thatched hut on the banks of the Gaṅgā in Nabadwīp, West Bengal, India. Later, through the service of his beloved associate-servitor and successor, Śrīla Govinda Mahārāj, the mission spread all over the world.</p>
                <p class="ks-font">
                    At the time of first establishing Śrī Chaitanya Sāraswat Maṭh, Śrīla Śrīdhar Mahārāj composed the following verse, encapsulating his mission’s ideal and predicting its future glory:
                </p>
                <p class="fw-bold ks-font">
                    śrīmach-chaitanya-sārasvata-maṭha-vara-udgīta-kīrttir-jayaśrīm<br />
                    bibhrat sambhāti gaṅgā-taṭa-nikaṭa-navadvīpa-kolādrirāje<br />
                    yatra śrī-gaura-sāraswata-mata-niratā gaura-gāthā gṛṇanti<br />
                    nityaṁ rūpānuga-śrī-kṛtamati-guru-gaurāṅga-rādhājitāśā
                </p>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center">
            <p class="ks-font">
                “In the holy abode of transcendental touchstone Śrī Nabadwīp Dhām—the selfsame Śrī Vṛndāvan, in the land of Koladwīp, whose inner identity is the king of mountains Śrī Govardhan, near the charming shores of the holy river Bhāgīrathī who delivers all the fallen souls, stands that grand and glorious king of all temples—Śrī Chaitanya Sāraswat Maṭh.
            </p>
            <p class="ks-font">
                In that beautiful retreat, the surrendered devotees whose lives are the message of Śrī Gaura, are eternally absorbed in the loving service of the Divine Master, the Golden Lord Gaurāṅga, and the Loving Lord Śrī Govindasundar with His consort Śrīmatī Gandharvā, accompanied by all Their beloved associates.
            </p>
            <p class="ks-font">
                With hearts always filled with the good hope to attain the grace of the Lord, following faithfully in the line of Śrī Rūpa and Raghunāth—the storekeepers of the unlimited treasure of pure, sweet love divine—those surrendered souls ever sing the unending glories of the transcendental Name and qualities of the most magnanimous, original Supreme Lord, Śrī Śrī Gaurāṅgasundar.
            </p>
            <p class="ks-font">
                Indeed the hearts of all beings, moving and still, marvel in the glory of Śrī Chaitanya Sāraswat Maṭh as they take shelter in the soothing, affectionate shade of the victory flag that flies aloft and sings wholeheartedly to the world the ever-expanding renown of Śrī Chaitanya Sāraswat Maṭh; bearing that banner of all transcendental benedictions, Śrī Chaitanya Sāraswat Maṭh ever shines resplendent in all its divine magnificence.”
            </p>
        </div>
        <div class="d-flex flex-column align-items-center guardians">
            <h1 class="ks-font">Our Guardians</h1>
            <div class="d-flex justify-content-center">
                <div class="d-flex flex-column align-items-center w-25 m-2 m-md-3 m-lg-5 m-xl-5">
                    <Pic :source="require('@/assets/img/KrishnaSearchGuardian1.png')" w="25vw" h="35vw"></Pic>
                    <p class="fw-bold ks-font pt-2 text-center">Śrīla Govinda Mahārāj</p>
                </div>
                <div class="d-flex flex-column align-items-center w-25 m-2 m-md-3 m-lg-5 m-xl-5">
                    <Pic :source="require('@/assets/img/KrishnaSearchGuardian2.png')" w="25vw" h="35vw"></Pic>
                    <p class="fw-bold ks-font pt-2 text-center">Śrīla Śrīdhar Mahārāj</p>
                </div>
                <div class="d-flex flex-column align-items-center w-25 m-2 m-md-3 m-lg-5 m-xl-5">
                    <Pic :source="require('@/assets/img/KrishnaSearchGuardian3.png')" w="25vw" h="35vw"></Pic>
                    <p class="fw-bold ks-font pt-2 text-center">Śrīla Saraswatī Ṭhākur</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Pic from '@/components/Pic.vue';

export default {
    components: { Pic }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

p {
    max-width: 900px;
    
    @include lg {
        margin: 20px 30px;
        font-size: 18px;
    }
    
    @include sm-md {
        margin: 10px 20px;
        padding: 0;
        font-size: 16px;
    }
}

.guardians {
    @include sm-md {
        padding-bottom: 100px;
    }
}
</style>
