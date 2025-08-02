<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\Employee;

class LineController extends Controller{
    public function merge(Request $request){
        $validatedData = Validator::make($request->all(),[
            'employeeId'=>'required',
            'password'=>'required',
            'lineUserId'=>'required',
            'lineDisplayName'=>'required',
        ]);
        $validated = $validatedData->validated();
        if($validatedData->fails()){
            return $this->sendError($validatedData->errors());
        }
        if($employee = Employee::where('employeeid',$validated['employeeId'])->where('password',$validated['password'])->first()){
            if(Employee::where('id',$employee->id)->update([
                'lineUserId' => $validated['lineUserId'],
                'lineDisplayName' => $validated['lineDisplayName'],
            ])){
                return $this->sendResponse([],true,'ระบบได้ทำการผูกข้อมูลแก่พนักงาน '.$employee->name.' '.$employee->lastname.' เสร็จสิ้น ');
            }else return $this->sendResponse([],false,'ระบบไม่พบข้อมูลพนักงาน กรุณาตรวจสอบอีกครั้ง');
        } else return $this->sendResponse([],false,'ระบบไม่พบข้อมูลพนักงาน หรือรหัสลับไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง');
    }
    public function sendResponse($result,$status, $message = 'Data successful'){
        $response = [
            'status' => $status,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }
    public function sendError($error, $errorMessages = [], $code = 500){
        $response = [
            'status' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
