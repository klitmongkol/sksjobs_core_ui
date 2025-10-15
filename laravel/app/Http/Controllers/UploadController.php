<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use ZipArchive;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UploadController extends Controller
{
    public function payroll(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded in the request.'], 400); 
        }
        $uploadedFile = $request->file('file');
        $mimeType = $uploadedFile->getClientMimeType();
        $allowedMimeTypes = [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-tar',
            'application/gzip',
        ];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return response()->json(['message' => 'Invalid file type. Only ZIP and TAR files are allowed.'], 400);
        }
        if ($mimeType !== 'application/zip' && $mimeType !== 'application/x-zip-compressed') {
            return response()->json(['message' => 'TAR file uploaded. Processing TAR files requires a different library (e.g., PharData). This controller currently only supports ZIP extraction.'], 400);
        }
        $path = $uploadedFile->storeAs('temp_uploads', 'uploaded_file_' . time() . '.zip', 'local');
        $fullPath = Storage::disk('local')->path($path);
        $zip = new ZipArchive;
        $fileList = [];
        if ($zip->open($fullPath) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (substr($filename, -1) !== '/') {
                        $fileList[] = [
                            'name' => $filename,
                            'size' => $zip->statIndex($i)['size'] ?? 0,
                            'is_dir' => false
                        ];
                } else {
                        $fileList[] = [
                            'name' => $filename,
                            'size' => 0,
                            'is_dir' => true
                        ];
                }
            }
            $zip->close();
            Storage::disk('local')->delete($path);
            return response()->json([
                'message' => 'Zip file processed successfully.',
                'total_files' => count($fileList),
                'files' => $fileList
            ]);
        } else {
            Storage::disk('local')->delete($path);
            Log::error('Could not open zip file for reading: ' . $fullPath);
            return response()->json(['message' => 'Failed to open the zip file.'], 500);
        }
    }
}
