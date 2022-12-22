<script setup>
import {ref} from 'vue';
import {Inertia} from '@inertiajs/inertia';
import {Head, Link} from '@inertiajs/inertia-vue3';

defineProps({
    title: String,
});


const sidebar = ref(false);

</script>

<template>
    <Head :title="title"/>


    <div class="header">
        <button class="burger-btn" @click.prevent="sidebar = !sidebar">
            <img src="/images/hamburger.png" alt="">
            menu
        </button>
    </div>

    <div class="sidebar" :class="{'active':sidebar}">
        <div class="sidebar-logo">
            <img src="/images/crawler.png" alt="">
            Web crawler
        </div>

        <div class="sidebar-items">
            <Link :href="route('dashboard')" class="sidebar-item" :class="{'active':route().current('dashboard')}">
                Əmlaklar
            </Link>
            <Link :href="route('favorites')" class="sidebar-item" :class="{'active':route().current('favorites')}">
                Yadda saxlanılmış əmlaklar
            </Link>
            <Link method="post" :href="route('logout')" class="sidebar-item">
                Çıxış
            </Link>
        </div>

        <div class="sidebar-close" @click.prevent="sidebar = false">
            <img src="/images/close.png" alt="">
        </div>
    </div>
    <div class="main">
        <slot>

        </slot>
    </div>
</template>
