<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class ImageToolsController extends Controller
{
    public function qrGenerator(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/image/QrGenerator');
    }

    public function pngToWebp(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/image/PngToWebp');

    }
    public function jpgToWebp(Request $request)
    {
        return Inertia::render('tools/image/JpgToWebp');
    }

    public function processPngToWebp(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:png|max:5120',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        $quality = (int) $request->input('quality', 80);
        $files = $request->file('images');
        $results = [];
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $tempDir = storage_path('app/public/temp/' . $batchId);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $webpName = $nameWithoutExt . '.webp';
            $webpPath = $tempDir . '/' . $webpName;

            $image = imagecreatefrompng($file->getRealPath());
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            imagewebp($image, $webpPath, $quality);
            imagedestroy($image);

            $results[] = [
                'original' => $originalName,
                'webp' => $webpName,
                'size' => round(filesize($webpPath) / 1024, 2),
                'reduction' => round((1 - (filesize($webpPath) / filesize($file->getRealPath()))) * 100, 1)
            ];
        }

        return response()->json(['batchId' => $batchId, 'results' => $results]);
    }

    public function processJpgToWebp(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg|max:5120',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        $quality = (int) $request->input('quality', 80);
        $files = $request->file('images');
        $results = [];
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $tempDir = storage_path('app/public/temp/' . $batchId);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $webpName = $nameWithoutExt . '.webp';
            $webpPath = $tempDir . '/' . $webpName;

            $image = imagecreatefromjpeg($file->getRealPath());
            imagewebp($image, $webpPath, $quality);
            imagedestroy($image);

            $results[] = [
                'original' => $originalName,
                'webp' => $webpName,
                'size' => round(filesize($webpPath) / 1024, 2),
                'reduction' => round((1 - (filesize($webpPath) / filesize($file->getRealPath()))) * 100, 1)
            ];
        }

        return response()->json(['batchId' => $batchId, 'results' => $results]);
    }

    public function downloadZip($id)
    {
        $tempDir = storage_path('app/public/temp/' . $id);
        if (!file_exists($tempDir)) {
            abort(404);
        }

        $zipFile = storage_path('app/public/temp/' . $id . '.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = glob($tempDir . '/*.webp');
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
    
}
