<script setup lang="ts">import ResourcePage from '../components/ResourcePage.vue'; import { resourceConfigs } from '../lib/resource-config';</script>
<template><ResourcePage resource="rooms" v-bind="resourceConfigs.rooms" /></template>
