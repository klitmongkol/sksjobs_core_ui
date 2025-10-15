<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">👷🏼 รายการข้อมูลพนักงาน (Employee List)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col">รหัสพนักงาน</th>
                            <th scope="col">เพศ</th>
                            <th scope="col">คำนำหน้า</th>
                            <th scope="col">ชื่อสกุล</th>
                            <th scope="col">line</th>
                            <th scope="col">plant</th>
                            <th scope="col">section</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- แสดง Loading Spinner ขณะกำลังโหลด -->
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted small">กำลังดึงข้อมูลพนักงาน...</p>
                            </td>
                        </tr>

                        <!-- แสดงรายการข้อมูลเมื่อโหลดเสร็จ -->
                        <tr v-else v-for="(employee, index) in employees" :key="employee.id">
                            <!-- คำนวณลำดับที่ถูกต้องตามหน้าปัจจุบัน -->
                            <td class="text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                            <td><span class="badge bg-secondary">{{ employee.employeeid }}</span></td>
                            <td>{{ employee.gender }}</td>
                            <td>{{ employee.prefix }}</td>
                            <td>{{ employee.name }}</td>
                            <td>{{ employee.line }}</td>
                            <td>{{ employee.plant }}</td>
                            <td>{{ employee.section }}</td>
                        </tr>

                        <!-- แสดง 'ไม่พบรายการ' เมื่อโหลดเสร็จและข้อมูลว่างเปล่า -->
                        <tr v-if="!loading && employees.length === 0">
                            <td colspan="6" class="text-center text-muted py-3">ไม่พบรายการ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between align-items-center bg-white border-top-0 py-3">
            
            <div class="d-flex align-items-center small text-muted">
                แสดง
                <select v-model="itemsPerPage" class="form-select form-select-sm mx-2 w-auto">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
                รายการต่อหน้า (รวม {{ totalItems.toLocaleString() }} รายการ)
            </div>

            <nav v-if="totalPages > 1">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="currentPage = Math.max(1, currentPage - 1)" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <li class="page-item"
                        v-for="page in pageRange" :key="page"
                        :class="{ active: currentPage === page }">
                        <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
                    </li>

                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="currentPage = Math.min(totalPages, currentPage + 1)" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
        </div>
    </div>
</template>

<script setup>
import axios from 'axios'; // ⬅️ นำเข้า Axios (ES Module Syntax)
import { computed, onMounted, ref, watch } from 'vue';

// State สำหรับเก็บข้อมูล
const employees = ref([]); // ข้อมูลพนักงานสำหรับหน้าปัจจุบัน
const totalItems = ref(0); // จำนวนรายการทั้งหมด (จาก Server Meta)
const totalPages = ref(1); // จำนวนหน้ารวมทั้งหมด (จาก Server Meta)
const loading = ref(true); // สถานะกำลังโหลด
const error = ref(null); // ข้อผิดพลาด

// State ของ Pagination
const itemsPerPage = ref(25);
const currentPage = ref(1);

/**
 * ฟังก์ชันสำหรับดึงข้อมูลสินค้าจาก API (Server-Side Pagination)
 */
const fetchProducts = async () => {
    loading.value = true;
    error.value = null;
    try {
        const jwtToken = localStorage.getItem('jwt_token');
        const response = await axios.post('/api/employee/list',
            {
                page: currentPage.value,
                limit: itemsPerPage.value
            },
            {
                headers: {
                    //  แนบ JWT Token ในรูปแบบ Bearer
                    'Authorization': `Bearer ${jwtToken}`
                }
            },
        );
        
        // ⬅️ กำหนดค่าข้อมูลที่ได้จาก API ไปยัง State
        employees.value = response.data.data || [];
        
        // ⬅️ ดึงข้อมูล Meta สำหรับ Pagination จาก Server
        if (response.data.meta) {
            // **การแก้ไขสำคัญ:** ใช้ 'total' แทน 'total_records' (คีย์มาตรฐานของ Laravel Paginator)
            totalItems.value = response.data.meta.total_records || 0;
            totalPages.value = response.data.meta.total_pages || 1;
            currentPage.value = response.data.meta.current_page || 1;
        } else {
            // Fallback กรณี API ไม่ส่ง meta มา
            totalItems.value = employees.value.length;
            totalPages.value = 1;
        }

    } catch (error) {
        console.log(error.response);
        if(error.response){
            switch(error.response.status){
                case 401 :
                    // กรณี Unauthorized (Token หมดอายุ)
                    logout();
                break;
                case 422 :
                    console.log(error.response.data.errors);
                    error.value = 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล';
                break;
                default :
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                    error.value = 'บันทึกข้อมูลไม่สำเร็จ โปรดตรวจสอบ Console และ API Endpoint';
                break;
            }
        }
        else{
            console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
            error.value = 'ไม่สามารถเชื่อมต่อกับ Server ได้';
        }
    } finally {
        loading.value = false; // ⬅️ สิ้นสุดสถานะกำลังโหลด
    }
};

// ⬅️ ใช้ onMounted เพื่อเรียกฟังก์ชันดึงข้อมูลเมื่อคอมโพเนนต์พร้อม
onMounted(() => {
    fetchProducts();
});

// ----------------------------------------------------
// Computed properties สำหรับ Pagination (Server-Side)
// ----------------------------------------------------

// ช่วงหมายเลขหน้าที่ต้องการแสดง
const pageRange = computed(() => {
    const range = [];
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);

    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        range.push(i);
    }
    return range;
});

const logout = () => {
    localStorage.clear();
    window.location.href = "/logout";
}

// Watcher สำหรับการดึงข้อมูลใหม่เมื่อเปลี่ยนหน้าหรือ limit
watch([itemsPerPage, currentPage], () => {
    // ป้องกันการเรียก API ซ้ำซ้อนถ้าหน้าปัจจุบันถูกรีเซ็ต
    if (currentPage.value === 0) {
        currentPage.value = 1;
        return;
    }
    fetchProducts();
});

// expose fetchProducts ให้ parent ใช้ ref เรียกได้
defineExpose({
    fetchProducts
});
</script>

<style scoped>
    /* Custom Style เพิ่มเติมเพื่อให้สวยงาม */
    .card-body {
        background-color: #f8f9fa; /* สีพื้นหลังอ่อนๆ */
    }
    .table-hover > tbody > tr:hover > * {
        --bs-table-accent-bg: var(--bs-primary-bg-subtle); /* เน้นสีเมื่อ Hover */
        color: var(--bs-primary-text-emphasis);
    }
    .form-select-sm {
        min-width: 80px; /* กำหนดความกว้างขั้นต่ำของ Select */
    }
</style>
