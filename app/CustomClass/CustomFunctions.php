<?php

namespace App\CustomClass;

use App\Models\Activitylog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomFunctions
{

    public static function EmailContentParser($content, $data)
    {
        $content = str_replace('{{ ', '{{', $content);
        $content = str_replace(' }}', '}}', $content);
        $parsed = preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($data) {
            list($shortCode, $index) = $matches;
            if (isset($data[$index])) {
                return $data[$index];
            } else {
                return '';
                //throw new Exception("Shortcode {$shortCode} not found in template id {$this->id}", 1);
            }
        }, $content);

        return $parsed;
    }

    public static function parseEmbeddedImageString($reqString, $folder_name)
    {
        $stt = $reqString; // string containing base64 encoded image files.
        preg_match('#data:image/(gif|png|jpeg);base64,([\w=+/]++)#', $stt, $x);
        while (isset($x[0])) {
            $imgdata = base64_decode($x[0]);
            $info = explode(";", explode("/", $x[0])[1])[0];
            $folderName = $folder_name;
            $safeName = Str::uuid() . '.png';
            $filewithpath = "storage/" . $folderName . '/' . $safeName;
            if (config('filesystems.default') == 's3') {
                Storage::disk('s3')->put($filewithpath, file_get_contents($x[0]));
                $stt = str_replace($x[0], Storage::disk('s3')->url($filewithpath), $stt);
            } else {
                Storage::disk('public')->put($folderName . '/' . $safeName, file_get_contents($x[0]));
                $stt = str_replace($x[0], asset('/' . $filewithpath), $stt);
            }
            preg_match('#data:image/(gif|png|jpeg);base64,([\w=+/]++)#', $stt, $x);
        }
        return $stt;
    }

    public static function addActivitylog($userid, $logtype, $title, $description, $createdby, $creationdate)
    {
        try {
            $activity_log = Activitylog::create([
                'user_id' => $userid,
                'log_type' => $logtype,
                'title' => $title,
                'description' => $description,
                'created_by' => $createdby,
                'creation_date' => $creationdate,
            ]);

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'activity_log' => $activity_log]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }

    public static function updateSlug($id, $name, $table)
    {
        try {
            if ($id > 0 && ($name != '' || $name != null) && ($table != '' || $table != null)) {

                $slug_string = $name . ' ' . $id;
    
                DB::table($table)
                    ->where('id', $id)
                    ->update(['slug' => Str::slug($slug_string, '-')]);
            }

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.slugupdated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }

}