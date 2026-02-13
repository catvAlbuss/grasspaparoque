<script setup>
import { computed, ref } from 'vue'

const images = [
    {
        id: '1',
        title: 'imagen 1',
        src: 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    },
    {
        id: '2',
        title: 'imagen 2',
        src: 'https://images.unsplash.com/photo-1439792675105-701e6a4ab6f0?q=80&w=1173&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    },
    {
        id: '3',
        title: 'imagen 3',
        src: 'https://images.unsplash.com/photo-1483982258113-b72862e6cff6?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    },
    {
        id: '4',
        title: 'imagen 4',
        src: 'https://images.unsplash.com/photo-1477346611705-65d1883cee1e?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    }

]

const current = ref(0)
const total = images.length

console.log('curren: ' + current)

const activeImage = computed(() => images[current.value])

const prev = () => {
    current.value = (current.value + 1) % total
    let items = document.querySelectorAll(".item");
    document.querySelector(".slide").prepend(items[items.length - 1]);
}

const next = () => {
    current.value = (current.value - 1 + total) % total
    let items = document.querySelectorAll(".item");
    document.querySelector(".slide").appendChild(items[0]);
}

</script>

<template>
    <section id="Galeria" class="relative flex min-h-128">
        <div class="contenedor">
            <div class="text-black">
                <h2 class="text-black-500">Galería de imágenes</h2>
            </div>
            <div class="slide">
                <!-- <img :src="activeImage.src" :alt="activeImage.title" > 
                 <div class="item" :style="{backgroundImage: `url(${activeImage.src})`}">     
                </div> 
                <div class="item" v-for="(imagen, index) in images" :key="imagen.id" :style="{backgroundImage: `url(${imagen.src})`}">
                </div>-->
                <div class="item"
                    style="background-image: url('https://scontent-lim1-1.xx.fbcdn.net/v/t39.30808-6/480691324_1244735060992850_7163497321851888994_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=86c6b0&_nc_eui2=AeEbPpeXCCXzx2YkkUBsGwl0cPIYc3GuN-1w8hhzca437bOH_Vn2XqZDJ53R6-27YdkE9WwJjrm3XcRPC_lG6JDK&_nc_ohc=ZQN7Rio4v3kQ7kNvwEXXmLL&_nc_oc=AdnLGVr2Q72YublCTWvP_qHXp8W7wCz4l3SW2xtXuqp2Hvp-bAYbreMWLcZSjSoltNIILPuAQ_reenEtuXQ53R9K&_nc_zt=23&_nc_ht=scontent-lim1-1.xx&_nc_gid=gPySXnlBNKBUxYPMexnz3Q&oh=00_AfsYikBo-QKuIMAT971gOm_S0kheNs9PNCh-5s1_UWA0lw&oe=6991A7CA');">
                </div>
                <div class="item"
                    style="background-image: url('https://scontent-lim1-1.xx.fbcdn.net/v/t39.30808-6/511006620_30036855455962802_6050749190109138726_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=833d8c&_nc_eui2=AeHJziertc4n_ZtdmpraD3dc21PYS1jm8ITbU9hLWObwhKucMktUYwsXeOpbU-AR1txgkRfoSrGXIKY5of66SJOz&_nc_ohc=_UGA9MUat-UQ7kNvwGKej6u&_nc_oc=AdnIuKSOJpLyexsjH3iQqN423a-T1ykSoMf_FZCb3WcAXaDPFpTnCN790Nk-WuzWDANe0A1hc3-VWU0gwhyEguTY&_nc_zt=23&_nc_ht=scontent-lim1-1.xx&_nc_gid=tmWNp9ADb0i9gUS7-Pqybg&oh=00_AfudXAv0zp2YhbVTzjtQ2ERCAhCfKYyXE6l-5GPshNczAw&oe=6991A566');">
                </div>
                <div class="item"
                    style="background-image: url('https://scontent-lim1-1.xx.fbcdn.net/v/t39.30808-6/511010785_30036855539296127_2736308905083674384_n.jpg?_nc_cat=108&ccb=1-7&_nc_sid=833d8c&_nc_eui2=AeGQ__a6RFdCqATGazBoFUG3Z9rmsCoassln2uawKhqyyRtkM6lrdVk9V4I-_BT4iE-1vHBc1o9MfVOLH8kcInIJ&_nc_ohc=e30GDEMLhGoQ7kNvwEEngMU&_nc_oc=AdknT7aIzuFpcLHP9xA3mfZb96DCdG3n6W0AeMI1thBi7y8AF2EBoKb5lTf7ScnTX4hMieVbmFKTSG4ODA5jY8x7&_nc_zt=23&_nc_ht=scontent-lim1-1.xx&_nc_gid=Ree0iVtSyS7WtSQAt11sxQ&oh=00_AftrZgwkiDyo_LzXBKgMntP8D2Kw7IkXx-ZkKata-zEIvA&oe=6991B8E1');">
                </div>
                <div class="item"
                    style="background-image: url('https://scontent-lim1-1.xx.fbcdn.net/v/t39.30808-6/511108269_30036855845962763_1450649314463257455_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=833d8c&_nc_eui2=AeHv-nsKtgm2LGj_9Ty9ekQQtopie76pH1e2imJ7vqkfV6dWdqOERazTi2v0Ebsy-X7f9nX5g1uY27o9j-opg1g2&_nc_ohc=7akua31Bh5cQ7kNvwH8SVSR&_nc_oc=AdkFQ5vxR0qcZeHXRcJgDY0_yt_Vo6nGcr3LI-2cLVESaqHEKMpYLJ5L3u3E8Gi9HaAnbX4s9uJF-OCnDxOQmRgS&_nc_zt=23&_nc_ht=scontent-lim1-1.xx&_nc_gid=4MA-6akI1PrYNzS2pX_syA&oh=00_AftO2uitkg3MY3e8Sdzcv8Wvup2vP34_cBquL_gPGzvOzA&oe=6991B3D9');">
                </div>
            </div>
            <div class="button">
                <button class="prev" @click="prev">◁</button>
                <button class="next" @click="next">▷</button>
            </div>
        </div>

    </section>
</template>

<style scoped>
.contenedor {
    /* display: flex; */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 400px;
    background: #f5f5f5;
    /* box-shadow: 0 30px 50px #dbdbdb; */
    border-radius: 20px;
}

.contenedor .slide {
    /* display: flex; */
    border-radius: 20px;
    justify-content: center;
}

.contenedor .slide .item {
    width: 200px;
    height: 250px;
    position: absolute;
    top: 50%;
    transform: translate(0, -50%);
    border-radius: 20px;
    box-shadow: 0 30px 50px #505050;
    background-position: 50% 50%;
    background-size: cover;
    display: inline-block;
    transition: all 0.5s;
}

.slide .item:nth-child(1),
.slide .item:nth-child(2) {
    top: 0;
    left: 0;
    transform: translate(0, 0);
    border-radius: 0;
    width: 100%;
    height: 100%;
    border-radius: 20px;
    transition: all .5s;
}

.slide .item:nth-child(3) {
    left: 50%;
}

.slide .item:nth-child(4) {
    left: calc(50% + 220px);
}

/* .slide .item:nth-child(5) {
    left: calc(50% + 440px);
} */

.slide .item:nth-child(n + 5) {
    left: calc(50% + 440px);
    opacity: 0;
}

.item .content {
    position: absolute;
    top: 50%;
    left: 100px;
    width: 300px;
    text-align: left;
    color: #eee;
    transform: translate(0, -50%);
    font-family: system-ui;
    display: none;
}

.slide .item:nth-child(2) .content {
    display: block;
}

.content .name {
    font-size: 40px;
    text-transform: uppercase;
    font-weight: bold;
    opacity: 0;
    animation: animate 1s ease-in-out 1 forwards;
}

.content .des {
    margin-top: 10px;
    margin-bottom: 20px;
    opacity: 0;
    animation: animate 1s ease-in-out 0.3s 1 forwards;
}

@keyframes animate {
    from {
        opacity: 0;
        transform: translate(0, 100px);
        filter: blur(33px);
    }

    to {
        opacity: 1;
        transform: translate(0);
        filter: blur(0);
    }
}

.button {
    display: flex;
    flex-direction: row;
    gap: 20px;
    /* left: 45%;
    right: 50%; */
    width: 100%;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: absolute;
    bottom: 20px;
}

.button button {
    width: 40px;
    height: 35px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    margin: 0 5px;
    border: 2px solid #000000bd;
    transition: 0.3s;
    background: rgba(255, 255, 255, 0.578);
}

.button button:hover {
    color: #000000;
    border: 2px solid #ffffffbd;
    transform: scale(1.1);
}

.button button:focus {
    transform: scale(1.1);
    background: #ffffff;
    border: 2px solid #ffffffbd;
}

.button button:active {
    transform: scale(1.02);
}

.next {
    padding: 0 0 0 3px;
}

.prev {
    padding: 0 3px 0 0;
}

@media screen and (max-width: 768px) {

    .slide .item:nth-child(1),
    .slide .item:nth-child(2) {
        top: 0;
        left: 0;
        transform: translate(0, 0);
        width: 100%;
        height: 100%;
        border-radius: 16px;
        transition: all .4s;
    }

    /* las imágenes laterales ocultas */
    .slide .item:nth-child(n + 3) {
        display: none !important;
        opacity: 0;
        visibility: hidden;
    }
}
</style>