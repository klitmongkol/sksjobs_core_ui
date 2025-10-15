<template>
    <div class="text-center">
        <button v-if="!profile" @click="lineLogin" class="btn btn-success btn-lg">
            Login with Line
        </button>
    </div>

    <div v-if="profile" class="card mt-4">
        <div class="card-header bg-success text-white">
            <h3>Line Profile</h3>
        </div>
        <div class="card-body text-center">
            <img :src="profile.pictureUrl" alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px;">
            <p><strong>Display Name:</strong> {{ profile.displayName }}</p>
            <p v-if="profile.statusMessage"><strong>Status Message:</strong> {{ profile.statusMessage }}</p>
            <div class="input-group mb-3">
                <span class="input-group-text" >User ID</span>
                <input type="text" class="form-control" :value="profile.userId" readonly>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" >รหัสพนักงาน</span>
                <input type="text" class="form-control" placeholder="👉ใส่รหัสพนักงานที่นี่👈" id="employeeId" v-model="employeeId">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" >รหัสลับ</span>
                <input type="password" class="form-control" placeholder="👉ใส่รหัสลับที่นี่👈" id="password" v-model="password">
            </div>
            <button @click="logout" class="btn btn-outline-danger ma-3">Log Out</button> &nbsp;
            <button @click="mergeEmployeeId" class="btn btn-primary ma-3 ">ผูกข้อมูล line กับพนักงาน</button>
        </div>
    </div>
    
</template>

<script setup>
    import liff from "@line/liff";
import axios from 'axios';
import { onMounted, ref } from 'vue'; // เพิ่ม onUnmounted
    const profile = ref(null); // ตัวแปรสำหรับเก็บข้อมูลโปรไฟล์
    // *** สำคัญมาก: แทนที่ด้วย LIFF ID "ตัวเต็ม" ของคุณจาก LINE Developers Console (แท็บ LIFF) ***
    const liffId = ref('2007365702-6nVW5vlg');
    const employeeId = ref(''); // เพื่อผูกกับ input รหัสพนักงาน
    const password = ref('');
    const initializeLiff = async () => {
        try {
            //alert('initializeLiff');
            await liff.init({ liffId: liffId.value });
            if(liff.isLoggedIn()){
                console.log('Logedin');
                const accessToken = liff.getAccessToken();
                if(accessToken){
                    profile.value  = await liff.getProfile();
                    if(profile.value.userId){
                        localStorage.setItem('userId',profile.value.userId);
                        localStorage.setItem('displayName',profile.value.displayName);
                        localStorage.setItem("profile", JSON.stringify(profile.value));
                        await getLineProfileFromBackend({
                            accessToken : accessToken,
                            userId : profile.value.userId,
                        });
                    } else {
                        alert("UserId are not fround, but logged in via liff.");
                    }
                } else {
                    alert("No access token found, but logged in via LIFF. Perhaps ID Token is available?");
                }
            } else {
                console.log("Not logged in to LIFF.");
                await liff.login(); // LIFF จะ redirect ไปหน้า Line Login
            }
        } catch (error) {
            alert("LIFF initialization failed: " + error.message);
        }
    };
    const lineLogin = async () => {
        try {
            await liff.login(); // LIFF จะ redirect ไปหน้า Line Login
        } catch (error) {
            console.error("Line login failed:", error);
            alert("Line login failed: " + error.message);
        }
    };
    const getLineProfileFromBackend = async (parameter) => {
        try {
            // ตรวจสอบให้แน่ใจว่า URL นี้ถูกต้องสำหรับ Backend ของคุณ
            const response = await axios.post('/line/get/profile', {
                accessToken : parameter.accessToken,
                userId :parameter.userId
            });
            profile.value = response.data; // เก็บข้อมูลโปรไฟล์ที่ได้จาก Backend
            console.log('Profile from Backend:', profile.value);
        } catch (error) {
            console.error('Failed to get Line profile from backend:', error);
            alert('Failed to get Line profile: ' + (error.response?.data?.message || error.message));
        }
    };
    const logout = () => {
        if (liff.isLoggedIn()) {
            liff.logout();
            profile.value = null; // รีเซ็ตค่า profile เมื่อ logout
            alert('Logged out from Line.');
        }
    };
    
    const mergeEmployeeId = async () => {
        // ตรวจสอบข้อมูลเบื้องต้น
        if (!profile.value || !profile.value.userId) {
            alert('ไม่พบข้อมูล Line User ID กรุณาเข้าสู่ระบบ Line ก่อน');
            return;
        }
        if (!employeeId.value.trim()) { // .trim() เพื่อตัดช่องว่างหน้า-หลัง
            alert('กรุณากรอกรหัสพนักงาน');
            return;
        }
        if (!password.value.trim()) { // .trim() เพื่อตัดช่องว่างหน้า-หลัง
            alert('กรุณากรอกรหัสลับ');
            return;
        }
        try {
            // ดึงข้อมูลที่จำเป็นจาก localStorage หรือจาก profile.value
            const lineUserId = profile.value.userId; // ใช้จาก profile.value โดยตรง
            const lineDisplayName = profile.value.displayName;
            const employeepassword = password.value;
            // ส่งข้อมูลไปยัง Backend
            const response = await axios.post('/line/merge', { // *** กำหนด API Endpoint ของคุณที่นี่ ***
                lineUserId: lineUserId,
                employeeId: employeeId.value,
                lineDisplayName: lineDisplayName,
                password: employeepassword,
            });
            if (response.data.status) {
                
                alert(response.data.message);
            } else {
                alert('ผูกข้อมูลไม่สำเร็จ: ' + (response.data.message || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'));
            }
        } catch (error) {
            console.error('Error merging employee ID:', error);
            alert('เกิดข้อผิดพลาดในการผูกข้อมูล: ' + (error.response?.data?.message || error.message));
        }
    };
    
    onMounted( async () => {
        if(window.liff) { // ตรวจสอบว่า liff SDK ถูกโหลดแล้ว
            liff.init({ liffId: liffId.value }).then(() => {
                console.log('LIFF SDK is ready and initialized successfully!');
                initializeLiff(); // เรียก Logic การ initialize LIFF ที่แยกไว้
            }).catch((err) => {
                console.log(err.code, err.message);
                console.error("LIFF initialization failed:", err); // แก้ไขเป็น err
                alert("LIFF SDK loading failed. Please check your LIFF ID and LIFF URL in LINE Developers Console. Error: " + err.message);
            });
        } else {
            alert("window.liff is not available. Ensure LIFF SDK is loaded correctly and not blocked.");
        }
    });
</script>

<style scoped>
.btn-lg {
    padding: 15px 30px;
    font-size: 1.5em;
}
.rounded-circle {
    border: 2px solid #00b900; /* Line Green */
}
</style>