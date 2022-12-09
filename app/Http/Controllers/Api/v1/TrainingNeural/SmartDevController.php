<?php

namespace App\Http\Controllers\Api\v1\TrainingNeural;

use App\Http\Controllers\Controller;
use App\Services\MarkerApi;

class SmartDevController extends Controller
{
    public function send_dataset($dataset_id)
    {
        $link_id = 'ball';
        $mar = new MarkerApi();
        return $mar->createDataset($dataset_id, $link_id);
    }

    public function getStatuses()
    {
        $mar = new MarkerApi();
        return $mar->getStatuses();
    }
}
