<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as PathGenerators;

class MediaPathGenerator implements PathGenerators
{

    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        $date_created = strtotime($media[$media::CREATED_AT]);
        $year = date('Y', $date_created);
        $day = date('d', $date_created);
        $month = date('m', $date_created);
        return "{$year}/{$month}/{$day}/{$media->uuid}";
    }
}
