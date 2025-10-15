import { createRouter, createWebHistory } from 'vue-router';

import DashboardComponent from './components/layout/body/dashboard.vue';
import EmployeeComponent from './components/layout/body/employee.vue';
import HistoryComponent from './components/layout/body/history.vue';
import SalaryComponent from './components/layout/body/salary.vue';
import SettingComponent from './components/layout/body/setting.vue';
const routes = [
    { path: '/', component: DashboardComponent },
    { path: '/history', component: HistoryComponent },
    { path: '/salary', component: SalaryComponent },
    { path: '/employee', component: EmployeeComponent },
    { path: '/setting', component: SettingComponent },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;