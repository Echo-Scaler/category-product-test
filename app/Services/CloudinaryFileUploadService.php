<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class CloudinaryFileUploadService
{
    private $uploadApi;

    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $uploadApi = $this->uploadApi();

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

    public function deleteByUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $publicId = $this->extractPublicId($url);
        if (! $publicId) {
            return false;
        }

        try {
            $this->uploadApi()->destroy($publicId, ['resource_type' => 'image']);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function uploadApi(): UploadApi
    {
        if ($this->uploadApi instanceof UploadApi) {
            return $this->uploadApi;
        }

        // Configure Cloudinary client => that is given in .env
        $config = new Configuration;
        $config->cloud->cloudName = env('CLOUDINARY_CLOUD_NAME');
        $config->cloud->apiKey = env('CLOUDINARY_API_KEY');
        $config->cloud->apiSecret = env('CLOUDINARY_API_SECRET');
        $config->url->secure = true;

        $this->uploadApi = new UploadApi($config);

        return $this->uploadApi;
    }

    private function extractPublicId(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');
        $uploadPos = strpos($path, '/upload/');
        if ($uploadPos === false) {
            return null;
        }

        $afterUpload = substr($path, $uploadPos + strlen('/upload/'));
        $afterUpload = preg_replace('#^v[0-9]+/#', '', $afterUpload);
        if (! $afterUpload) {
            return null;
        }

        $publicId = preg_replace('#\.[a-zA-Z0-9]+$#', '', $afterUpload);

        return $publicId ?: null;
    }
}
