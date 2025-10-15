<template>
    <div class="p-4 flex-grow-1">
        <div class="card">
            <h5 class="card-header">อัพโหลดไฟล์เงินเดือนพนักงาน</h5>
            <div class="card-body">
                <h5 class="card-title">อัพโหลดไฟล์</h5>
                <p class="card-text">รองรับไฟล์ **.zip, .tar**</p>
                <div class="input-group">
                    <input
                        type="file"
                        class="form-control"
                        id="zipFileInput"
                        aria-describedby="uploadButton"
                        aria-label="Upload"
                        @change="handleFileChange"
                        accept=".zip,.tar"
                    >
                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        id="uploadButton"
                        @click="uploadFile"
                        :disabled="!selectedFile"
                    >
                        - อัพโหลด -
                    </button>
                </div>
                <div v-if="uploadStatus" :class="statusClass" class="mt-3 p-2 border rounded">
                    {{ uploadStatus }}
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="standby-tab" data-bs-toggle="tab" data-bs-target="#standby-tab-pane" type="button" role="tab" aria-controls="standby-tab-pane" aria-selected="true">รายชื่อที่พร้อมจัดส่งข้อความ</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="log-tab" data-bs-toggle="tab" data-bs-target="#log-tab-pane" type="button" role="tab" aria-controls="log-tab-pane" aria-selected="false" >ประวัดการจัดส่งข้อความ</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="standby-tab-pane" role="tabpanel" aria-labelledby="standby-tab" tabindex="0">
                            <salary-standby-component></salary-standby-component>
                        </div>
                        <div class="tab-pane fade" id="log-tab-pane" role="tabpanel" aria-labelledby="log-tab" tabindex="0">
                            <salary-log-component></salary-log-component>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, ref } from 'vue';

// ตัวแปรสำหรับเก็บไฟล์ที่ถูกเลือก
const selectedFile = ref(null);
// ตัวแปรสำหรับแสดงสถานะการอัปโหลด
const uploadStatus = ref('');
const isError = ref(false);

// กำหนด Class สำหรับสถานะ (ใช้ Bootstrap colors)
const statusClass = computed(() => {
    return isError.value ? 'alert-danger' : 'alert-success';
});

/**
 * จัดการเมื่อมีการเลือกไฟล์
 * @param {Event} event
 */
const handleFileChange = (event) => {
    // ดึงไฟล์แรกที่ผู้ใช้เลือก
    selectedFile.value = event.target.files ? event.target.files[0] : null;
    uploadStatus.value = selectedFile.value ? `ไฟล์พร้อมอัพโหลด: ${selectedFile.value.name}` : '';
    isError.value = false;
};

/**
 * จัดการกระบวนการอัปโหลดไฟล์
 */
const uploadFile = async () => {
    if (!selectedFile.value) {
        uploadStatus.value = 'กรุณาเลือกไฟล์ก่อนอัพโหลด';
        isError.value = true;
        return;
    }

    uploadStatus.value = 'กำลังอัพโหลด...';
    isError.value = false;

    // 1. สร้าง FormData Object เพื่อส่งไฟล์แบบ multipart/form-data
    const formData = new FormData();
    // 'file' คือชื่อฟิลด์ที่ฝั่ง Server (Laravel) จะใช้รับไฟล์
    formData.append('file', selectedFile.value);
    
    // กำหนด URL ของ API ที่จะรับไฟล์ (สมมติว่าเป็น /api/upload/payroll)
    const uploadUrl = '/api/upload/payroll';

    const jwtToken = localStorage.getItem('jwt_token');
    try {
        // 2. ใช้ axios.post เพื่อส่ง FormData
        const response = await axios.post(uploadUrl, formData, {
            // สำคัญ: การใช้ FormData มักไม่จำเป็นต้องกำหนด 'Content-Type'
            // เพราะ Axios และ Browser จะจัดการ header นี้ให้อัตโนมัติและถูกต้อง
            // แต่คุณอาจกำหนด headers อื่นๆ เช่น Token ได้ที่นี่
            headers: {
                // ต้องระบุ Content-Type เพื่อให้ Laravel รู้ว่าข้อมูลใน body เป็น JSON
                //'Content-Type': 'application/json',
                //  แนบ JWT Token ในรูปแบบ Bearer
                'Authorization': `Bearer ${jwtToken}`
            },
            // (ไม่จำเป็น) สำหรับแสดงความคืบหน้า (Progress Bar)
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadStatus.value = `กำลังอัพโหลด... ${percentCompleted}%`;
            }
        });

        uploadStatus.value = `อัพโหลดสำเร็จ! Server ตอบกลับ: ${JSON.stringify(response.data)}`;
        isError.value = false;
        
    } catch (error) {
        isError.value = true;
        // การจัดการข้อผิดพลาด (Error Handling)
        if (error.response && error.response.data) {
            uploadStatus.value = `อัพโหลดล้มเหลว: ${error.response.data.message || 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์'}`;
        } else {
            uploadStatus.value = `อัพโหลดล้มเหลว: ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้`;
        }
    }
};
</script>