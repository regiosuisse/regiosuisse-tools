<template>
    <router-view v-slot="{ Component }">
        <component :is="Component"></component>
    </router-view>
</template>

<script>
    export default {}
</script>