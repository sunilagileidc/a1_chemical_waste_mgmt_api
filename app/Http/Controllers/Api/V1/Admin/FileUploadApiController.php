<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Image;
use Log;

class FileUploadApiController extends Controller
{
    public function __construct(Request $request)
    {
        $locale = $request->input('lang');
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
    }
    /**
     * @function: to upload image details.
     *
     * @author: Santhosha G
     *
     * @created-on: 9 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function imageUpload(Request $request)
    {
        try {
            Log::info($request);
            if (request('image')) {
                $base64_str = substr($request->image, strpos($request->image, ",") + 1);

                //decode base64 string
                if ($request->width > 1600) {
                    $orig_image = base64_decode($base64_str);
                    $image = Image::make($orig_image)->resize($request->width, $request->height, function ($constraint) {
                        $constraint->aspectRatio();
                    })->stream('png', 100);
                } else {
                    $image = base64_decode($base64_str);
                }

                $folderName = $request->folder;
                $safeName = Str::uuid() . '.' . $request->extension;
                if (config('filesystems.default') == 's3') {
                    Storage::disk('s3')->put('storage/' . $folderName . '/' . $safeName, $image);
                } else {
                    Storage::disk('public')->put($folderName . '/' . $safeName, $image);
                }
                //Storage::disk('public')->put($folderName.'/'.$safeName, $image);
                //$storagePath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
                $filewithpath = "/storage/" . $folderName . '/' . $safeName;
                //$p = Storage::disk('s3')->put('images/others/' . $safeName, $image, 'public');
                //$image_url = Storage::disk('s3')->url('images/others/' .$safeName);

                $status = 'S';
                $message = 'File Uploaded Successfully';
            } else {
                $status = 'E';
                $filewithpath = '';
                $message = 'File size too large, try file with less then 15MB';
            }
            return response()->json(['status' => $status, 'message' => $message, 'filepath' => $filewithpath]);
        } catch (\Exception $file_exception) {
            Log::info($file_exception->getmessage());

            return response()->json(['status' => 'E', 'message' => 'Error Uploading file ' . $file_exception->getmessage(), 'filepath' => '']);
        }
    }

    /**
     * @function: to image Url Base64 details.
     *
     * @author: Santhosha G
     *
     * @created-on: 9 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function imageUrlBase64(Request $request)
    {
        try {
            $image = base64_encode(file_get_contents(str_replace(' ', '+', $request->url)));
            return response()->json(['imagedata' => 'data:image/png;base64,' . $image]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => $e->getmessage()]);
        }
    }

    public function fileUpload(Request $request)
    {
        try {

            if (!File::exists(storage_path('app/public/' . $request->folder))) {
                File::makeDirectory(storage_path('app/public/' . $request->folder));
            }

            if (request('image')) {
                $base64_str = substr($request->image, strpos($request->image, ",") + 1);
                //decode base64 string
                $image = base64_decode($base64_str);
                $folderName = $request->folder;
                $safeName = $request->filename . '_' . date('dmYhis') . '.' . $request->extension;
                Storage::disk('public')->put($folderName . '/' . $safeName, $image);
                $storagePath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
                $filewithpath = "/storage/" . $folderName . '/' . $safeName;
                $status = 'S';
                $message = 'File Uploaded Successfully';
            } else {
                $status = 'E';
                $filewithpath = '';
                $message = 'File size too large, try file with less then 1MB';
            }
            return response()->json(['status' => $status, 'message' => $message, 'filepath' => $filewithpath]);
        } catch (Exception $file_exception) {
            return response()->json(['status' => 'E', 'message' => config('errorcode.LA-244') . 'Error Uploading file ' . $file_exception->getmessage(), 'filepath' => '']);
        }
    }
}
