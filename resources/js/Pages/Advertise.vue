<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";
import 'vue3-carousel/dist/carousel.css'
import {Carousel, Slide, Pagination, Navigation} from 'vue3-carousel'

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

</script>
<template>
    <AppLayout title="Advertise">
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
            <carousel :items-to-show="1.5">
                <slide v-for="(image,key) in entity.images" :key="key">
                    <img :src="image" width="500" alt="">
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

            <a @click.prevent="deleteAdvertise" :href="route('advertises.destroy',entity.id)"
               class="advertise-delete">
                Elanı sil
            </a>
        </div>
    </AppLayout>
</template>
