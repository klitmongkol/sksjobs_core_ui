<template>
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;">
        <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
            <span class="fs-4">SKS Company</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item" v-for="menu in menus" :key="menu.id">
                <a
                    href="#"
                    class="nav-link text-white"
                    :class="{ active: activeMenu === menu.id }"
                    @click.prevent="setActiveMenu(menu.id)"
                    aria-current="page"
                >
                    <span :class="menu.icon"></span> {{ menu.text }}
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <strong>by klit-developer.dev</strong>
        </div>
    </div>
    <div class="b-example-divider"></div>
</template>

<script setup>
import { defineEmits, ref } from 'vue';

const emits = defineEmits(['change-component']);
const activeMenu = ref(1);

const menus = [
    { id: 1, text: 'Dashboard', icon: 'mdi mdi-home', component: 'dashboard-component' },
    { id: 2, text: 'ข้อมูลย้อนหลัง', icon: 'mdi mdi-list-box-outline', component: 'history-component' },
    { id: 3, text: 'เงินเดือนพนักงาน', icon: 'mdi mdi-account-cash-outline', component: 'salary-component' },
    { id: 4, text: 'พนักงาน', icon: 'mdi mdi-account-outline', component: 'employees-component' },
    { id: 5, text: 'ตั้งค่า', icon: 'mdi mdi-file-cog-outline', component: 'settings-component' },
];

const setActiveMenu = (id) => {
    activeMenu.value = id;
    const selectedMenu = menus.find(menu => menu.id === id);
    if (selectedMenu) {
        emits('change-component', selectedMenu.component);
    }
};
</script>