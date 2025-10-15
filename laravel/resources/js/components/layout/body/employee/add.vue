<template>
    <p class="mt-3 text-muted">💡 คัดลอกข้อมูลพนักงานจาก Excel/Spreadsheet แล้วมาวาง (Ctrl+V) ที่ช่องแรกได้เลย</p>
    <div class="table-responsive-container">
        <div class="spreadsheet-header grid-row-layout">
            <div v-for="(header, index) in fieldHeaders" :key="index" class="grid-cell header-cell">
                <span>{{ header }}</span>
            </div>
        </div>
        
        <div class="spreadsheet-grid">
            <div v-for="(row, rowIndex) in tableData" :key="rowIndex" class="grid-row-layout">
                <div v-for="(cell, colIndex) in row" :key="colIndex" class="grid-cell data-cell">
                    <input
                        type="text"
                        v-model="tableData[rowIndex][colIndex]"
                        :ref="el => { if (rowIndex === 0 && colIndex === 0) firstCellRef = el }"
                        @paste="rowIndex === 0 && colIndex === 0 ? handlePaste($event) : null"
                    >
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <button class="btn btn-secondary" @click="resetData">
            รีเซ็ตข้อมูล
        </button>
        <button class="btn btn-primary" @click="submitData" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ isSubmitting ? 'กำลังบันทึก...' : 'บันทึกข้อมูลทั้งหมด' }}
        </button>
    </div>
    <div class="modal fade" id="resultModal" ref="modalRef" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true" :class="{ 'show d-block': isModalVisible }">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">{{ modalTitle }}</h5>
                    <button type="button" class="btn-close" @click="hideModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p v-html="modalBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="hideModal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>
    <div v-if="isModalVisible" class="modal-backdrop fade show"></div>

    <!-- Loading Overlay -->
    <div v-if="isSubmitting" class="loading-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-white fw-bold">กำลังอัปโหลดข้อมูล โปรดรอสักครู่...</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref } from 'vue';

const fieldKeys = ['employeeid', 'name', 'accountno', 'startedwork', 'section', 'line', 'plant', 'serialno', 'password', 'email'];
const fieldHeaders = ['รหัส', 'ชื่อ - สกุล', 'เลขที่บัญชี', 'Started Work', 'Section (PD/PC/QM)', 'Line', 'Plant (SB/NK)', 'เลขบัตรประชาชน', 'Password', 'email'];
const initialCols = fieldKeys.length;
const initialRows = 5;

const tableData = ref(
    Array(initialRows).fill(null).map(() =>
        Array(initialCols).fill('')
    )
);
const firstCellRef = ref(null);
const isSubmitting = ref(false);

const modalRef = ref(null);
let bsModalInstance = null;
const isModalVisible = ref(false);
const modalTitle = ref('');
const modalBody = ref('');

const handlePaste = (event) => {
    event.preventDefault();
    const pastedText = event.clipboardData.getData('text');
    if (!pastedText) return;
    const rows = pastedText.trim().split(/\r?\n/).filter(r => r.trim() !== '');
    if (rows.length === 0) return;
    const pastedRowsData = rows.map(row =>
        row.split('\t').map(cell => cell.trim())
    );
    const newRowCount = pastedRowsData.length;
    const expectedColCount = fieldKeys.length;
    const actualColCount = pastedRowsData[0].length;
    if (newRowCount > tableData.value.length) {
        const diff = newRowCount - tableData.value.length;
        tableData.value.push(...Array(diff).fill(null).map(() =>
            Array(expectedColCount).fill('')
        ));
    }
    pastedRowsData.forEach((pastedRow, rowIndex) => {
        pastedRow.forEach((cellValue, colIndex) => {
            if (colIndex < expectedColCount && tableData.value[rowIndex] && tableData.value[rowIndex][colIndex] !== undefined) {
                tableData.value[rowIndex][colIndex] = cellValue;
            }
        });
    });
    if (actualColCount > expectedColCount) {
        console.warn(`เตือน: ข้อมูลที่ Paste มี ${actualColCount} คอลัมน์ แต่ระบบต้องการ ${expectedColCount} คอลัมน์ ข้อมูลส่วนเกินถูกละเว้น`);
    }
};

const submitData = async () => {
    isSubmitting.value = true;
    const validData = tableData.value.filter(row => row.some(cell => cell.trim() !== ''));
    if (validData.length === 0) {
        alert("กรุณาวางข้อมูลพนักงานก่อนบันทึก!");
        isSubmitting.value = false;
        return;
    }
    let hasEmptyCell = false;
    let emptyRowIndex = -1;
    let emptyColHeader = '';
    for (let rowIndex = 0; rowIndex < validData.length; rowIndex++) {
        const row = validData[rowIndex];
        for (let colIndex = 0; colIndex < row.length; colIndex++) {
            if (row[colIndex].trim() === '') {
                hasEmptyCell = true;
                emptyRowIndex = rowIndex + 1;
                emptyColHeader = fieldHeaders[colIndex];
                break;
            }
        }
        if (hasEmptyCell) break;
    }
    if (hasEmptyCell) {
        alert(`⚠️ บันทึกข้อมูลไม่สำเร็จ: พบช่องว่างในแถวที่ ${emptyRowIndex} คอลัมน์ "${emptyColHeader}" กรุณากรอกข้อมูลให้ครบถ้วน!`);
        isSubmitting.value = false;
        return;
    }
    const dataToSend = validData.map(row => {
        let rowObject = {};
        fieldKeys.forEach((key, index) => {
            rowObject[key] = row[index] || '';
        });
        return rowObject;
    });
    try {
        const jwtToken = localStorage.getItem('jwt_token');
        const response = await axios.post('/api/employee/add',
            { employees: dataToSend },
            {
                headers: {
                    'Authorization': `Bearer ${jwtToken}`
                }
            },
        );
        if(response.data.status == 'success'){
            alert(`บันทึกข้อมูลสำเร็จ! เพิ่มพนักงาน ${response.data.imported_rows} คน`);
        }else{
            alert(`${response.data.message}`);
        }
        resetData();
    } catch (error) {
        if(error.response){
            switch(error.response.status){
                case 401 :
                    alert('Unauthorized 🛑 เซสชันหมดอายุ หรือโทเค็นไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่');
                    logout();
                break;
                case 422 :
                    console.log(error.response.data.errors);
                    showModal('บันทึกข้อมูลไม่สำเร็จ',error.response.data.message + ' หรือ ข้อมูลที่อัพโหลด อาจมีข้อมูลซ้ำซ้อนกันเอง โปรดตรวจสอบแล้วลองอีกครั้ง.');
                break;
                default :
                    console.error('เกิดข้อผิดพลาดในการบันทึกข้อมูล:', error);
                    alert('บันทึกข้อมูลไม่สำเร็จ โปรดตรวจสอบ Console และ API Endpoint');
                break;
            }
        }
        else{
            console.error('เกิดข้อผิดพลาดในการบันทึกข้อมูล:', error);
            alert('บันทึกข้อมูลไม่สำเร็จ โปรดตรวจสอบ Console และ API Endpoint');
        }
    } finally {
        isSubmitting.value = false;
    }
};

const resetData = () => {
    tableData.value = Array(initialRows).fill(null).map(() => 
        Array(initialCols).fill('')
    );
};

const showModal = (title, body) => {
    modalTitle.value = title;
    modalBody.value = body;
    isModalVisible.value = true;
    if (window.bootstrap && modalRef.value) {
        if (!bsModalInstance) {
            bsModalInstance = new window.bootstrap.Modal(modalRef.value);
        }
        bsModalInstance.show();
    }
};

const hideModal = () => {
    if (bsModalInstance) {
        bsModalInstance.hide();
    }
    setTimeout(() => {
        isModalVisible.value = false;
    }, 150);
};

const logout = () => {
    localStorage.clear();
    window.location.href = "/logout";
}
</script>

<style scoped>
.table-responsive-container {
    overflow-x: auto;
    border: 1px solid #ccc;
    margin-top: 10px;
}
.spreadsheet-header {
    background-color: #f8f9fa;
    font-weight: bold;
    border-bottom: 1px solid #ccc;
}
.grid-row-layout {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(10, minmax(120px, 1fr));
}
.grid-cell {
    padding: 0;
    border-right: 1px solid #eee;
    border-bottom: 1px solid #eee;
}
.grid-cell:last-child {
    border-right: none;
}
.header-cell {
    background-color: #e9ecef;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    height: 40px;
}
.spreadsheet-grid .grid-row-layout:last-child .grid-cell {
    border-bottom: none;
}
.grid-cell input {
    width: 100%;
    box-sizing: border-box;
    border: none;
    padding: 8px;
    margin: 0;
    line-height: 1.5;
    text-align: center;
}
.grid-cell input:focus {
    border: 1px solid #007bff;
    outline: none;
    z-index: 10;
}
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
}
.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
