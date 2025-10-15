<template>
    <div class="p-4 flex-grow-1">
        <div class="card">
            <h5 class="card-header">พนักงาน</h5>
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">รายชื่อพนักงาน</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">เพิ่มพนักงาน</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                        <employees-List-component ref="listRef" ></employees-List-component>
                    </div>
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                        <employees-Add-component></employees-Add-component>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { onMounted, ref } from 'vue';
    const listRef = ref(null);
    const handleTabShown = (event) => {
        // ตรวจสอบว่า tab ที่ถูก active คือ home-tab
        if(event.target.id === 'home-tab') {
            listRef.value?.fetchProducts();
        }
    };
    onMounted(() => {
        const homeTabEl = document.getElementById('home-tab');
        if(homeTabEl){
            homeTabEl.addEventListener('shown.bs.tab', handleTabShown);
        }
    });
</script>