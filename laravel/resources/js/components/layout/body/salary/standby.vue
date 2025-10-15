<template>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">👷‍♂️ รายชื่อพนักงานที่พร้อมจัดส่งเอกสาร 📨 ( / )</h5>
        </div>
        <div class="card-body p-0">
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col">รหัสพนักงาน</th>
                            <th scope="col">ชื่อเอกสาร</th>
                            <th scope="col" class="text-center">ผลการจับคู่</th>
                            <th scope="col" class="text-end">ราคา/หน่วย (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(product, index) in paginatedData" :key="product.id">
                            <td class="text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                            <td><span class="badge bg-secondary">{{ product.sku }}</span></td>
                            <td>{{ product.name }}</td>
                            <td class="text-center">
                                <span :class="{'badge rounded-pill': true, 'bg-success': product.stock > 50, 'bg-warning text-dark': product.stock <= 50}">
                                    {{ product.stock.toLocaleString() }}
                                </span>
                            </td>
                            <td class="text-end fw-bold">{{ product.price.toFixed(2).toLocaleString() }}</td>
                        </tr>
                        <tr v-if="paginatedData.length === 0">
                            <td colspan="5" class="text-center text-muted py-3">ไม่พบรายการสินค้า</td>
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
                        <a class="page-link" href="#" @click.prevent="currentPage--" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <li class="page-item"
                        v-for="page in pageRange" :key="page"
                        :class="{ active: currentPage === page }">
                        <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
                    </li>

                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="currentPage++" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

// ข้อมูลจำลอง (ตัวอย่าง 120 รายการ)
const rawProducts = Array.from({ length: 120 }, (_, i) => ({
    id: i + 1,
    sku: `PROD-${String(i + 1).padStart(4, '0')}`,
    name: `สินค้าชิ้นที่ ${i + 1}`,
    stock: Math.floor(Math.random() * 100) + 1,
    price: parseFloat((Math.random() * 500 + 10).toFixed(2)),
}));

// State ของ Pagination
const itemsPerPage = ref(25); // จำนวนรายการที่เลือก
const currentPage = ref(1);    // หมายเลขหน้าปัจจุบัน

// จำนวนรายการทั้งหมด
const totalItems = computed(() => rawProducts.length);

// จำนวนหน้ารวม
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

// ข้อมูลที่แสดงผลในหน้าปัจจุบัน
const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    // ป้องกันการเลือกหน้าเกินขอบเขต
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value || 1;
    }
    return rawProducts.slice(start, end);
});

// ช่วงหมายเลขหน้าที่ต้องการแสดง (เช่น 1, 2, 3, 4, ...)
const pageRange = computed(() => {
    const range = [];
    // กำหนดจำนวนปุ่มหน้าที่จะแสดง (เช่น 5 ปุ่ม)
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);

    // ปรับ startPage อีกครั้งเมื่อ endPage ถูกจำกัดด้วย totalPages
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        range.push(i);
    }
    return range;
});

// Watcher เพื่อรีเซ็ตหน้าเมื่อ itemsPerPage เปลี่ยน
import { watch } from 'vue';
watch(itemsPerPage, () => {
    currentPage.value = 1;
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