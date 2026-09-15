<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\ImageSize;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use Illuminate\Support\Facades\File;
use App\Services\ImageService;

class ImageResizeController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function __invoke($size, $fit, $path)
    {
        try {
            return $this->imageService->resizeImage($size, $fit, $path);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }
}
