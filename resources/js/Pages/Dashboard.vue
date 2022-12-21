<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {ref} from "vue";
import Multiselect from '@vueform/multiselect'
import {Link, useForm} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";

const options = ref(['1', '2', '3', '4', '5'])
const value = ref(null);

const props = defineProps({
    advertises: {
        required: true
    },
    ziggy: {
        required: true
    }
})


const filterForm = useForm({
    search: props.ziggy?.query.search,
    price_min: props.ziggy?.query.price_min,
    price_max: props.ziggy?.query.price_max,
    address: props.ziggy?.query.address,
    room_count: props.ziggy?.query.room_count,
    area_m_min: props.ziggy?.query.area_m_min,
    area_m_max: props.ziggy?.query.area_m_max,
    area_sot_min: props.ziggy?.query.area_sot_min,
    area_sot_max: props.ziggy?.query.area_sot_max,
})

const submitFilterForm = () => {
    filterForm.get(route('dashboard'));
}

const deleteAdvertise = (id) => {
    if (confirm('Elanı silmək istədiyinizə əminsinizmi ?')) {
        Inertia.delete(route('advertises.destroy', id))
    }
}

</script>

<template>
    <AppLayout title="Dashboard">
        <h1>{{ advertises.total }} əmlak tapıldı</h1>

        <div class="filter">
            <div class="filter-search">
                <input v-model="filterForm.search" placeholder="Axtarış..." type="text">
                <button @click.prevent="submitFilterForm">
                    <img src="/images/filter.png" alt="">
                </button>
            </div>

            <div class="filter-forms">
                <div class="form-group">
                    <label for="">
                        Otaq sayı
                    </label>
                    <Multiselect
                        placeholder="Otaq sayı"
                        mode="multiple"
                        v-model="filterForm.room_count"
                        :options="options"
                    />
                </div>
                <div class="filter-group">
                    <div class="form-group">
                        <label for="">
                            Qiymət ( Min )
                        </label>
                        <input v-model="filterForm.price_min" placeholder="200 AZN" type="text">
                    </div>
                    <div class="form-group">
                        <label for="">
                            Qiymət ( Max )
                        </label>
                        <input v-model="filterForm.price_max" placeholder=" 500 AZN" type="text">
                    </div>
                </div>
                <div class="filter-group">
                    <div class="form-group">
                        <label for="">
                            Sahə m2 (min)
                        </label>
                        <input v-model="filterForm.area_m_min" placeholder="Min" type="text">
                    </div>

                    <div class="form-group">
                        <label for="">
                            Sahə m2 (max)
                        </label>
                        <input v-model="filterForm.area_m_max" placeholder="Max" type="text">
                    </div>
                </div>
                <div class="filter-group">
                    <div class="form-group">
                        <label for="">
                            Sahə sot (min)
                        </label>
                        <input v-model="filterForm.area_sot_min" placeholder="Min" type="text">
                    </div>

                    <div class="form-group">
                        <label for="">
                            Sahə sot (max)
                        </label>
                        <input v-model="filterForm.area_sot_max" placeholder="Max" type="text">
                    </div>
                </div>
            </div>

            <table>
                <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th>
                        Əmlak
                    </th>
                    <th>
                        Qiyməti
                    </th>
                    <th>
                        Otaq sayı
                    </th>
                    <th>
                        Sənəd
                    </th>
                    <th>
                        Ünvan
                    </th>
                    <th>
                        Telefon nömrəsi
                    </th>
                    <th>
                        Mülkiyyətçi
                    </th>
                    <th>
                        Əməliyyatlar
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="advertise in advertises.data">
                    <td>
                        {{ advertise?.id }}
                    </td>
                    <td>
                        <template v-if="advertise['images']">
                            <div class="image-container">
                                <img :src="advertise['images'][0]" alt="">
                            </div>
                        </template>
                    </td>
                    <td>
                        {{ advertise.price }} AZN
                    </td>
                    <td>
                        {{ advertise.room_count }}
                    </td>
                    <td>
                        {{ advertise.document_type }}
                    </td>
                    <td style="width: 150px">
                        {{ advertise.address }}
                    </td>
                    <td>
                        <template v-for="phone in advertise.phones">
                            {{ phone }}
                        </template>
                    </td>
                    <td>
                        {{ advertise.username }}
                    </td>
                    <td>
                        <div style="display: flex;">
                            <Link style="margin-right: 10px" :href="route('advertises.show',advertise.id)"><img
                                width="20" height="20" src="/images/right-arrow.png" alt=""></Link>
                            <Link @click.prevent="deleteAdvertise(advertise.id)"><img width="20" height="20"
                                                                                      src="/images/trash.png" alt="">
                            </Link>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <Pagination :links="advertises.links"/>

        </div>
    </AppLayout>
</template>
<style src="@vueform/multiselect/themes/default.css"></style>
