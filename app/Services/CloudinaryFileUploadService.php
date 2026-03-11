<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryFileUploadService
{
    private $uploadApi;

    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        // Configure Cloudinary client => that is given in .env
        $config = new Configuration;
        $config->cloud->cloudName = env('CLOUDINARY_CLOUD_NAME');
        $config->cloud->apiKey = env('CLOUDINARY_API_KEY');
        $config->cloud->apiSecret = env('CLOUDINARY_API_SECRET');
        $config->url->secure = true;

        $uploadApi = new UploadApi($config);

        $result = $uploadApi->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'auto',
            // to upload video or image => use auto
        ]);

        if (! isset($result['secure_url'])) {
            throw new RuntimeException('Failed to upload file to Cloudinary');
        }

        return $result['secure_url'];
    }
}
