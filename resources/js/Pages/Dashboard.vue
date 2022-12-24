<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {computed, ref} from "vue";
import Multiselect from '@vueform/multiselect'
import {Link, useForm} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";

const options = ref(['1', '2', '3', '4', '5'])
const value = ref(null);

const props = defineProps({
    advertises: {
        required: true
    },
    districts: {
        required: true
    },
    categories: {
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
    repair: props.ziggy?.query.repair,
    document_type: props.ziggy?.query.document_type,
    category: props.ziggy?.query.category,
    district: props.ziggy?.query.district,
})

const submitFilterForm = () => {
    filterForm.get(props.ziggy.location);
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

        <form class="filter">
            <div class="filter-search">
                <input v-model="filterForm.search" placeholder="Axtarış..." type="text">
                <button type="submit" @click.prevent="submitFilterForm">
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

                <div class="filter-group filter-group__mobile">
                    <div class="form-group form-group__checkbox">
                        <label for="">
                            Təmir
                        </label>

                        <div class="form-group-wrapper">
                            <label for="repair_1">

                                <input v-model="filterForm.repair" true-value="all" value="all" checked id="repair_1"
                                       name="repair"
                                       type="radio">
                                <span>
Hamısı
                                </span>
                            </label>
                            <label for="repair_2">

                                <input v-model="filterForm.repair" true-value="var" id="repair_2" name="repair"
                                       value="var" type="radio">
                                <span>
Təmirli
                                </span>
                            </label>
                            <label for="repair_3">

                                <input v-model="filterForm.repair" true-value="yoxdur" id="repair_3" name="repair"
                                       value="yoxdur" type="radio">
                                <span>
Təmirsiz
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group form-group__checkbox">
                        <label for="">
                            Sənəd
                        </label>

                        <div class="form-group-wrapper">
                            <label for="repair_4">

                                <input v-model="filterForm.document_type" true-value="all" value="all" checked
                                       id="repair_4"
                                       name="document" type="radio">
                                <span>
Hamısı
                                </span>
                            </label>
                            <label for="repair_5">

                                <input v-model="filterForm.document_type" true-value="var" id="repair_5" name="document"
                                       value="var" type="radio">
                                <span>
Var
                                </span>
                            </label>
                            <label for="repair_6">

                                <input v-model="filterForm.document_type" true-value="yoxdur" id="repair_6"
                                       name="document"
                                       value="yoxdur" type="radio">
                                <span>
Yox
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="filter-group">
                    <div class="form-group">
                        <label for="">
                            Rayon
                        </label>
                        <Multiselect
                            placeholder="Rayon"
                            v-model="filterForm.district"
                            :searchable="true"
                            :options="districts"
                        />
                    </div>
                    <div class="form-group">
                        <label for="">
                            Kateqoriya
                        </label>
                        <Multiselect
                            placeholder="Kateqoriya"
                            v-model="filterForm.category"
                            :searchable="true"
                            :options="categories"
                        />
                    </div>
                </div>

            </div>

            <table class="table-in-desktop">
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
            <table class="table-in-mobile">
                <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th>
                        Əmlak
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="advertise in advertises.data">
                    <td>
                        {{ advertise?.id }}
                    </td>
                    <td style="display: flex;align-items: center;flex-direction: column;">
                        <template v-if="advertise['images']">
                            <div class="image-container">
                                <img :src="advertise['images'][0]" alt="">
                            </div>
                        </template>
                        <Link :href="route('advertises.show',advertise.id)"
                              style="text-align: center;margin-top:10px;font-weight: 600;font-size: 13px;">
                            {{ advertise.name }}
                        </Link>
                    </td>
                </tr>
                </tbody>
            </table>

            <Pagination :links="advertises.links"/>

        </form>
    </AppLayout>
</template>
<style src="@vueform/multiselect/themes/default.css"></style>
