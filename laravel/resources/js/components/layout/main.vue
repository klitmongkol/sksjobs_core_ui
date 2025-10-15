<template>
    <div class="d-flex" style="min-height: 100vh;">
        <layout-sidemenu @change-component="updateComponent"></layout-sidemenu>
        <div class="d-flex flex-column flex-grow-1">
            <layout-navmenu :user="user"></layout-navmenu>
            <div class="p-4 flex-grow-1">
                <component :is="currentView" ></component>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import LayoutNavmenu from './nav.vue';
import LayoutSidemenu from './sidemenu.vue';

// รับ props ที่ชื่อ 'user' เข้ามา
const props = defineProps({
    user: {
        type: Object,
        required: true,
    }
});

// หากต้องการเข้าถึงข้อมูลใน script
const user = props.user;

// สร้างตัวแปร state เพื่อเก็บชื่อของ component ที่จะแสดงผล
const currentView = ref('dashboard-component');

// ฟังก์ชันสำหรับอัปเดตตัวแปร state เมื่อได้รับ event จาก sidemenu
const updateComponent = (componentName) => {
    currentView.value = componentName;
};
</script>