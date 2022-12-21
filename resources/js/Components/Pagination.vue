<template>
    <nav class="pagination" v-if="links.length > 3">
        <template v-for="(link, p) in links" :key="p">
            <span v-if="link.url === null"
                  v-html="link.label"/>
            <Link v-else
                  :class="{ 'active': link.active }" :href="link.url"
                  v-html="link.label"/>
        </template>
    </nav>
</template>

<script setup>
import {Link} from '@inertiajs/inertia-vue3';

defineProps({
    links: [Array, Object]
})
</script>
