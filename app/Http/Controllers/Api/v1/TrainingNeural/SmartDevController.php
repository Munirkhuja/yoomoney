<?php

namespace App\Http\Controllers\Api\v1\TrainingNeural;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatasetRequest;
use App\Models\Dataset;
use App\Services\MarkerApi;
use Psy\Util\Str;

class SmartDevController extends Controller
{
    public function send_dataset($dataset_id)
    {
        $mar = new MarkerApi();
        return $mar->createDataset($dataset_id, Dataset::findOrFail($dataset_id)->link_id);
    }
    public function getStatuses()
    {
        $mar = new MarkerApi();
        return $mar->getStatuses();
    }
}
