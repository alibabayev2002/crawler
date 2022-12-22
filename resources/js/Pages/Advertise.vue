<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";
import 'vue3-carousel/dist/carousel.css'
import {Carousel, Slide, Pagination, Navigation} from 'vue3-carousel'
import {ref} from "vue";

const props = defineProps({
    entity: {
        required: true,
        type: Object
    }
})

const deleteAdvertise = () => {
    if (confirm('Elanı silmək istədiyinizə əminsinizmi ?')) {
        Inertia.delete(route('advertises.destroy', props.entity.id))
    }
}

const scaleImageSrc = ref();

const scaleImage = (image) => {
    scaleImageSrc.value = image;
}

</script>
<template>
    <AppLayout title="Advertise">
        <div v-if="scaleImageSrc" class="scale-image-modal">
            <button @click.prevent="scaleImageSrc = null" class="scale-image-modal-close">
                <img src="/images/close.png" alt="">
            </button>
            <img :src="scaleImageSrc" alt="">
        </div>
        <div class="advertise">
            <div class="breadcrumb">
                <Link :href="route('dashboard')" class="breadcrumb-item active" href="">
                    Elanlar
                </Link>
                <span> / </span>
                <span class="breadcrumb-item">
                    {{ entity.id }}
                </span>
            </div>
            <carousel :items-to-show="1" :touchDrag="true">
                <slide v-for="(image,key) in entity.images" :key="key">
                    <div @click.prevent="scaleImage(image)" style="width: 100%;height: 500px;">
                        <img style="object-fit: cover;width: 100%;height: 100%" :src="image" alt="">
                    </div>
                </slide>

                <template #addons>
                    <navigation/>
                    <pagination/>
                </template>
            </carousel>

            <table>
                <tbody>
                <tr>
                    <td>Elanın nömrəsi</td>
                    <td>{{ entity.id }}</td>
                </tr>
                <tr>
                    <td>Otaq sayı</td>
                    <td>{{ entity.room_count }}</td>
                </tr>
                <tr>
                    <td>
                        Sənəd
                    </td>
                    <td>
                        {{ entity.document_type }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Təmir
                    </td>
                    <td>
                        {{ entity.repair }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Sahə
                    </td>
                    <td>
                        {{ entity.area }}
                    </td>
                </tr>
                <tr v-if="entity.land">
                    <td>
                        Torpaq sahəsi
                    </td>
                    <td>
                        {{ entity.land }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Ünvan
                    </td>
                    <td>
                        {{ entity.address }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Telefon nömrəsi
                    </td>
                    <td>
                        <template v-for="phone in entity.phones">
                            {{ phone }}
                        </template>
                    </td>
                </tr>
                <tr>
                    <td>
                        Qiymət
                    </td>
                    <td>
                        {{ entity.price }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Mülkiyyətçi
                    </td>
                    <td>
                        {{ entity.username }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Elan adı
                    </td>
                    <td>
                        {{ entity.name }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Açıqlama
                    </td>
                    <td>
                        {{ entity.description }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Crawler link
                    </td>
                    <td>
                        <a target="_blank" :href="entity.url">
                            {{ entity.url }}
                        </a>
                    </td>
                </tr>
                <tr v-for="item in entity.additional">
                    <td>
                        {{ item.key }}
                    </td>
                    <td>
                        {{ item.value }}
                    </td>
                </tr>
                </tbody>
            </table>

            <iframe
                width="100%"
                height="300"
                frameborder="0"
                scrolling="no"
                marginheight="0"
                marginwidth="0"
                :src="'https://maps.google.com/maps?q='+entity.latitude+','+entity.longitude+'&hl=es&z=14&amp;output=embed'"
            >
            </iframe>
            <br>

            <a target="_blank"
               :href="'https://www.google.com/maps/dir/?api=1&destination=' + entity.latitude + ',' + entity.longitude">
                Xəritədə aç
            </a>


            <br>
            <br>
            <Link :preserveScroll="true" method="post" :href="route('advertises.favorite',entity.id)">
                <template v-if="entity.is_favorite">
                    Yaddaşdan sil
                </template>
                <template v-else>
                    Yadda saxla
                </template>
            </Link>
            <br>

            <a @click.prevent="deleteAdvertise" :href="route('advertises.destroy',entity.id)"
               class="advertise-delete">
                Elanı sil
            </a>
        </div>
    </AppLayout>
</template>
