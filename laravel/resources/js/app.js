import { createApp } from 'vue';
import '../css/app.css';

// นำเข้า Component ทั้งหมด
import LayoutMain from './components/layout/main.vue';
import LayoutNavmenu from './components/layout/nav.vue';
import LayoutSidemenu from './components/layout/sidemenu.vue';
import LineLoginButton from './components/LineLoginButton.vue';
import LoginComponentForm from './components/LoginComponentForm.vue';

// นำเข้า Component ที่เป็นเนื้อหา
import DashboardComponent from './components/layout/body/dashboard.vue';


import HistoryComponent from './components/layout/body/history.vue';


import SalaryLogComponent from './components/layout/body/salary/log.vue';
import SalaryComponent from './components/layout/body/salary/main.vue';
import SalaryStandbyComponent from './components/layout/body/salary/standby.vue';

import EmployeeAddComponent from './components/layout/body/employee/add.vue';
import EmployeeListComponent from './components/layout/body/employee/list.vue';
import EmployeeComponent from './components/layout/body/employee/main.vue';

import SettingComponent from './components/layout/body/setting.vue';

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp({});
    
    // ลงทะเบียน Component ที่เป็นส่วนประกอบหลัก
    app.component('line-login-button', LineLoginButton);
    app.component('login-component-form', LoginComponentForm);
    app.component('layout-main', LayoutMain);
    app.component('layout-navmenu', LayoutNavmenu);
    app.component('layout-sidemenu', LayoutSidemenu);
    
    // ลงทะเบียน Component ที่เป็นเนื้อหาสำหรับ Dynamic Component
    app.component('dashboard-component', DashboardComponent);
    app.component('history-component', HistoryComponent);

    app.component('salary-component', SalaryComponent);
    app.component('salary-standby-component', SalaryStandbyComponent);
    app.component('salary-log-component', SalaryLogComponent);

    app.component('employees-component', EmployeeComponent);
    app.component('employees-List-component', EmployeeListComponent);
    app.component('employees-Add-component', EmployeeAddComponent);

    app.component('settings-component', SettingComponent);

    app.mount('#app');
});