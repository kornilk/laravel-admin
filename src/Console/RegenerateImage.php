<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;
use Encore\Admin\Models\Image;
use Intervention\Image\Facades\Image as InterventionImage;
use Intervention\Image\Constraint;
use Illuminate\Support\Facades\Storage;

class RegenerateImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:regenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $storage = null;

    public function __construct()
    {
        $this->storage = Storage::disk(config('admin.upload.disk'));
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $images = Image::all();

        foreach ($images as $image) {
            $this->generateThumbnails($image);
            $this->setFormat($image);
            $image->save();
        }
    }

    protected function generateThumbnails($image)
    {
        $file = $this->storage->path($image->path);
        $path = pathinfo($image->path);

        foreach (config('image.thumbnails') as $name => $size) {
            
            $thumbPath = "{$path['dirname']}/{$path['filename']}-{$name}.{$image->extension}";

            $imageObject = InterventionImage::make($file);

            $action = $size[2] ?? 'resize';
            $position = $size[3] ?? 'center';
            $upscale = $size[4] ?? false;
            // Resize image with aspect ratio
            $imageObject->$action($size[0], $size[1], function (Constraint $constraint) use ($upscale) {
                $constraint->aspectRatio();
                if ($upscale) $constraint->upsize();
            }, $position);

            $this->storage->put("/{$thumbPath}", $imageObject->encode());
        }
    }

    protected function setFormat($image)
    {
        $path_parts = pathinfo($image->path);
        foreach (config('image.thumbnails') as $key => $thumb) {

            $thumb_path = $this->storage->path($path_parts['dirname'] . '/' . $path_parts['filename'] . '-' . $key . '.' . $path_parts['extension']);
            list($width, $height) = getimagesize($thumb_path);

            $thumbnails[$key] = [
                'path' => $path_parts['dirname'] . '/' . $path_parts['filename'] . '-' . $key . '.' . $path_parts['extension'],
                'width' => $width,
                'height' => $height,
            ];
        }
        $image->formats = json_encode($thumbnails);
    }
}
