<?php

namespace App\Helpers;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AwsHelper
{
    public static function uploadFile($file, $path)
    {
        try {
            // $path sholud follow like that for both local and s3 == 'user/profile/'
            if (env("APP_ENV") == "local") {
                $fileName = rand() . '_' . time() . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->putFileAs($path, $file, $fileName);
                return Storage::url($path . $fileName);
            }
            $fileName = $path . rand() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $s3 = new S3Client([
                'region' => env('AWS_DEFAULT_REGION'),
                'version' => 'latest',
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
            $result = $s3->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $fileName,
                'SourceFile' => $file->getPathname(),
                // Removed 'ACL' => 'public-read'
            ]);

            if ($result['@metadata']['statusCode'] === 200) {
                return $fileName;
            }
        } catch(AwsException $ex) {
            Log::error($ex);
            return null;
        } catch(Exception $ex) {
            Log::error($ex);
            return null;
        }
    }
}
