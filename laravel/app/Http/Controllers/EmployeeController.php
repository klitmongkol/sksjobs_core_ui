<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\Employee;
use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;

class EmployeeController extends Controller
{
    public function list(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของพารามิเตอร์ (Validation)
        // กำหนดค่าเริ่มต้น (Default) ให้ page=1 และ limit=25 หากไม่ได้ส่งมา
        $validatedData = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100', // กำหนด Max Limit เพื่อความปลอดภัย
        ]);
        if ($validatedData->fails()) {
            // ใช้ sendError ของคุณ หรือส่ง Response 422
            return response()->json(['message' => 'Invalid parameters', 'errors' => $validatedData->errors()], 422);
        }
        // กำหนดค่า page และ limit โดยใช้ค่าที่ส่งมา หรือใช้ค่า Default
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 25);
        try {
            // 2. ใช้ฟังก์ชัน paginate() ของ Eloquent
            // นี่คือหัวใจสำคัญ: Laravel จะจัดการ OFFSET และ LIMIT ให้เอง
            $employees = Employee::
                // ->where('status', 'active') // (ถ้ามีเงื่อนไขเพิ่มเติม)
                // ->orderBy('id', 'desc')     // (ถ้าต้องการเรียงลำดับ)
                paginate($limit, ['*'], 'page', $page);
            // 3. จัดโครงสร้าง Response ให้เป็นรูปแบบมาตรฐาน
            return response()->json([
                'status' => 'success',
                'message' => 'Employees retrieved successfully.',
                // 4. ข้อมูล Metadata และ Data จะอยู่ใน $employees->toArray()
                // paginate() จะสร้างโครงสร้างข้อมูลที่จำเป็นทั้งหมดให้แล้ว
                // ข้อมูลเหล่านี้รวมถึง total, current_page, last_page, per_page, data
                'data' => $employees->items(), // ข้อมูลรายการพนักงานในหน้าปัจจุบัน
                'meta' => [
                    'current_page' => $employees->currentPage(),
                    'page_size' => (int) $employees->perPage(), // จำนวนต่อหน้า
                    'total_records' => $employees->total(),     // <--- จำนวนรายการทั้งหมด
                    'total_pages' => $employees->lastPage(),
                    'next_page_url' => $employees->nextPageUrl(),
                    'prev_page_url' => $employees->previousPageUrl(),
                ]
            ], 200);

        } catch (\Exception $e) {
            // ควรมีการจัดการ Error ที่ดีขึ้น
            return $e->getMessage();
            /*
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
            */
        }
    }

    /**
     * รับข้อมูล array ของพนักงานเพื่อบันทึกแบบกลุ่ม (Bulk Add)
     * ใช้วิธี Inline Validation ด้วย Validator::make
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        // 1. กำหนดกฎการตรวจสอบ (Rules)
        $rules = [
            // ตรวจสอบตัวแปรหลัก: ต้องมีและต้องเป็น array
            'employees' => ['required', 'array'],
            // ตรวจสอบสมาชิกแต่ละตัวใน array 'employees' โดยใช้ employees.*
            //'employees.*.employeeid' => ['required', 'string', 'max:20', 'unique:employee,employeeid', 'distinct'],
            'employees.*.employeeid' => ['required', 'string', 'max:20'],
            'employees.*.name' => ['required', 'string', 'max:100'],
            'employees.*.accountno' => ['required', 'string', 'max:20'],
            'employees.*.startedwork' => ['required', 'date'],
            'employees.*.section' => ['required', 'string', 'max:50'],
            'employees.*.line' => ['required', 'string', 'max:50'],
            'employees.*.plant' => ['required', 'string', 'max:10'],
            'employees.*.serialno' => ['required', 'string', 'max:20'],
            'employees.*.password' => ['required', 'string', 'min:6'],
            'employees.*.email' => ['nullable', 'email', 'max:100'],
        ];
        // 2. สร้าง Validator Instance
        $validator = Validator::make($request->all(), $rules);
        // 3. ตรวจสอบว่า Validation ล้มเหลวหรือไม่
        if ($validator->fails()) {
            // ถ้าล้มเหลว ให้ส่ง Response ข้อผิดพลาด 422 กลับไปที่ Client
            return response()->json([
                'message' => 'ข้อมูลที่ส่งมาไม่ผ่านการตรวจสอบ',
                'errors' => $validator->errors()
            ], 422);
        }
        // 4. ถ้า Validation ผ่าน
        // ดึงข้อมูลที่ผ่านการตรวจสอบแล้วออกมา (จะอยู่ในรูป Array)
        // Note: validated() จะคืนค่าเฉพาะ Field ที่ถูกกำหนดใน rules เท่านั้น
        $validatedData = $validator->validated();
        $employeesData = $validatedData['employees'];
        $importedCount = 0;
        $totalCount = 0;
        // 5. วนลูปเพื่อบันทึกข้อมูลพนักงาน
        foreach ($employeesData as $employee) {
            // Hash Password ก่อนบันทึกเสมอ!
            $totalCount++;
            // ---------- แยกคำนำหน้า ----------
            $name = trim($employee['name']);
            $gender = null;
            $prefix = null;

            if (str_starts_with($name, 'นาย')) {
                $gender = 'ชาย';
                $prefix = 'นาย';
                $name = trim(str_replace('นาย', '', $name));
            } elseif (str_starts_with($name, 'นางสาว')) {
                $gender = 'หญิง';
                $prefix = 'นางสาว';
                $name = trim(str_replace('นางสาว', '', $name));
            } elseif (str_starts_with($name, 'นาง')) {
                $gender = 'หญิง';
                $prefix = 'นาง';
                $name = trim(str_replace('นาง', '', $name));
            }
            // ---------- แยกชื่อ–นามสกุล ----------
            // กรณีไม่มีนามสกุล จะให้ lastname เป็นค่าว่าง
            $nameParts = preg_split('/\s+/', $name);
            $firstname = $nameParts[0] ?? '';
            $lastname = $nameParts[1] ?? '';

            // ---------- เตรียมข้อมูลก่อนบันทึก ----------
            $employee['prefix'] = $prefix ?? 'ไม่ระบุ';
            $employee['firstname'] = $firstname;
            $employee['lastname'] = $lastname;
            $employee['gender'] = $gender ?? 'ไม่ระบุ';
            $employee['name'] = $name;
            $employee['password'] = Hash::make($employee['password']);
            try {
                Employee::create($employee);
                $importedCount++;
            } catch (\Exception $e) {
                return $e->getMessage();
                //\Log::error("Failed to add employee: " . $e->getMessage(), ['data' => $employee]);
                // ในสถานการณ์จริง ควรมีการจัดการ error ที่ซับซ้อนกว่านี้
            }
        }
        // 6. ส่งผลลัพธ์กลับไปที่ Client
        return response()->json([
            'message' => 'บันทึกข้อมูลพนักงานทั้งหมดสำเร็จ',
            'imported_rows' => $importedCount,
            'total_data' => $totalCount,
            'status' => 'success'
        ], 200);
    }
}
