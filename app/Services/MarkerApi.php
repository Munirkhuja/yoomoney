<?php

namespace App\Services;


use App\Models\Dataset;
use App\Models\Label;
use App\Models\Polygon;
use App\Traits\ConnectSendTrait;

class MarkerApi
{
    use ConnectSendTrait;

    public function getDatasets()
    {
        return $this->send('GET', '/WebMarker/datasets');
    }

    public function removeDataset($id)
    {
        $this->send('POST', '/WebMarker/removeDataset', [
            'body' => json_encode([
                "name" => "$id"
            ])
        ]);
    }

    public function createDataset($id, $linkID)
    {
        $this->removeDataset($linkID);
        $dataset = Dataset::where('id', $id)->first();

        if ($dataset) {
            $ds = [
                "annotation" => [
                    "chanel" => 3,
                    "description" => "",
                    "height" => -1,
                    "labels" => [
                        "count" => 0,
                        "names" => []
                    ],
                    "name" => $linkID,
                    "preview_index" => 0,
                    "type" => 1,
                    "width" => -1
                ]
            ];

            $allLabels = [];
            $labels = Label::where('dataset_id', $id)->get()->toArray();

            foreach ($labels as $lbl) {
                $allLabels[$lbl['id']] = $lbl["name"];
                $ds["annotation"]["labels"]["names"][] = $lbl["name"];
                $ds["annotation"]["labels"]["count"]++;
            }


            /*нужен дальше */
            $labelsSettings = $ds["annotation"]["labels"];
            $labelsSettings['label_list'] = [];

            // создаем датасет
            $this->send('POST', '/WebMarker/addDataset', [
                'body' => json_encode($ds)
            ]);

            // заливаем картинки
            $datasetImages = [];
            $images = $dataset->getMedia();

            if (count($images)) {
                foreach ($images as $img) {
                    $imageUrl = $img->getUrl();
                    $datasetImages[] = $imageUrl;
                    $this->addImage($linkID, $imageUrl);
                }


                // заливаем разметку
                $p = [];
                $datasetPolygons = [];
                $polygons = Polygon::where('dataset_id', $id)->get()->toArray();
                if (count($polygons)) {
                    foreach ($polygons as $poly) {
                        $p[$poly['image_id']] = json_decode($poly['data'], true);
                    }

                    //перебор картинок и сборка полигонов
                    $imgCounter = 0;
                    foreach ($images as $img) {
                        // если у картинки есть полигоны
                        if (isset($p[$img->id])) {
                            $labelData = [
                                "index" => $imgCounter,
                                "label" => [],
                                "name" => $linkID
                            ];
                            // перебираем лейблы с полигонами
                            foreach ($p[$img->id] as $k => $v) {
                                $key = trim($k, 'l');

                                // если у лейбла есть полигоны
                                if (count($v)) {
                                    $type = array_search($allLabels[$key], $ds["annotation"]["labels"]["names"]);
                                    foreach ($v as $polygon) {
                                        $labelData["label"][] = [
                                            "polygon" => $polygon,
                                            "type" => $type
                                        ];
                                    }

                                    /* подготовка лейблов для обучения, выкинуть лишние, без разметки*/
                                    $labelsSettings['label_list'][$type] = $type;


                                }
                            }

                            // если что то есть - отправляем
                            if (count($labelData["label"])) {
                                $this->setLabelData($labelData);
                                echo '<pre>';
                                print_r($labelData);
                                echo '</pre>';
                            }

                        }
                        $imgCounter++;
                    }
                }

            }

            $labelsSettings['label_list'] = array_values($labelsSettings['label_list']);

            return $this->startTrain($linkID, $labelsSettings);


            /*echo '<pre>';
            print_r($datasetImages);
            echo '</pre>';*/


            /*echo '<pre>';
            print_r($ds);
            echo '</pre>';*/


            /*$images  = $dataset->GetImages(true);
            echo '<pre>';
            print_r($images);
            echo '</pre>';


            echo '<pre>';
            print_r($labels);
            echo '</pre>';*/
        }

        //return $labelsSettings;


        /*

        // получить все картинки

        $this->addImage(1, 'http://ii.local/999b2e64fbd11beb13f2130bf1a16cc9.jpg');


        return $response->getBody()->getContents();*/
    }

    public function addImage($datasetID, $src)
    {
        $image = $this->getBase64Image($src);
        $size = getimagesize($src);

        $data = [
            "data" => [
                "height" => $size[1],
                "label" => null,
                "src" => $image,
                "width" => $size[0],
            ],
            "name" => $datasetID
        ];

        $this->send('POST', '/WebMarker/addSrcData', ["body" => json_encode($data)]);
    }

    public function getDataset($name)
    {
        return $this->getDatasets()['datasets']["$name"] ?? null;
    }

    public function getImage($set, $id)
    {
        $response = $this->send('POST', '/WebMarker/datasetItem', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(["index" => (int)$id, "name" => $set])
        ]);
        return $response;
    }

    public function updateAnnotation($id)
    {
        $this->send('POST', '/WebMarker/updateAnnotationDataset', ["body" => json_encode([
            "labels" => [
                "count" => 3,
                "names" => [
                    "one",
                    "two",
                    "three"
                ]
            ],
            "name" => "ds_" . $id,
        ])]);
    }

    public function setLabelData($data)
    {
        $this->send('POST', '/WebMarker/setLabelData', ["body" => json_encode($data)]);
    }

    public function getStatuses()
    {
        $data = $this->send('POST', '/WebMarker/getTrainDashboard', ["body" => json_encode([])]);
        return $data;
        $m = [];
        if (count($data['models_list'])) {
            foreach ($data['models_list'] as $model) {
                $m[$model["config"]["name"]] = [
                    "full" => $model["config"]["loop"]["config"]["epohchCount"],
                    "now" => 0,
                    "value" => 0
                ];

                if (isset($model["history"])) {
                    $last = array_pop($model["history"]);
                    $m[$model["config"]["name"]]["now"] = $last["epoch"];
                }

                $m[$model["config"]["name"]]["value"] = round(($m[$model["config"]["name"]]["now"] / $m[$model["config"]["name"]]["full"]) * 100, 0);

                echo '<pre>';
                print_r($m[$model["config"]["name"]]);
                echo '</pre>';

            }
        }

        $nm = Dataset::where('status', 'training')->get();

        if ($nm) {
            foreach ($nm as $model) {
                if (!isset($m[$model->link_id])) {
                    $model->status = 'complete';
                    $model->progress = 100;
                } else {
                    $model->progress = $m[$model->link_id]["value"];
                }

                $model->save();
            }
        }
        return response()->json($data);
    }

    public function startTrain($id, $labelsSettings)
    {
        $base = '{"config":{"description":"","loop":{"config":{"epohchCount":15},"moduls":{"dataLoaders":[{"IO":{"size":{"input":[0,0,0],"output":[0,0,0]}},"config":{"add_validation":true,"batchSize":2,"name":"ds_2","shuffle":true,"train":true,"type":1},"moduls":{"augmentation":[{"IO":{"size":{"input":[0,0,0],"output":[0,0,0]}},"config":{"p":0.5},"name":"HorizontalFlip"}],"transformsInput":[{"IO":{"size":{"input":[0,0,0],"output":[720,720,0]}},"config":{"height":720,"width":720},"name":"Resize"}],"transformsOutput":null}}]}},"model":{"IO":{"size":{"input":[720,720,0],"output":[720,720,0]}},"config":{"labels":{"count":4,"label_list":[0,2],"names":["Первый","Второй","третий","3eee"]},"name":"DeepLab3","parameters":{"losses":[{"config":{"weight":1},"name":"MSE"}],"metriks":[{"name":"Dice"}],"outputchannels":2}}},"name":"f4441633-65d4-4d59-94de-c901f14a0d61"}}';
        $base = json_decode($base, true);

        $base["config"]["loop"]["moduls"]["dataLoaders"][0]["config"]["name"] = $id;
        $base["config"]["model"]["config"]["labels"] = $labelsSettings;
        $base["config"]["name"] = $id;

        return $this->send('POST', '/WebMarker/startTrain', ["body" => json_encode($base)]);
    }


    public function predictImage($id, $img)
    {
        $src = $img;
        $image = $this->getBase64Image($src);
        $data = [
            "data" => $image,
            "model_name" => $id
        ];

        $response = $this->send('POST', '/WebMarker/predictImage', ["body" => json_encode($data)]);

        return $response;
    }

    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        if (!isset($pct)) {
            return false;
        }
        $pct /= 100;
        // Get image width and height
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        // Turn alpha blending off
        imagealphablending($src_im, false);

        //loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                //get current alpha value (represents the TANSPARENCY!)
                /*$colorxy = imagecolorat( $src_im, $x, $y );
                $alpha = ( $colorxy >> 24 ) & 0xFF;
                //calculate new alpha
                if( $minalpha !== 127 ){
                    $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
                } else {
                    $alpha += 127 * $pct;
                }*/
                //get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, 255, 255, 255, 100);
                //set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        // The image copy
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }


    function imagemask($image, $mask)
    {
        // получаем формат картинки
        $arrImg = explode(".", $image);
        $format = (end($arrImg) == 'jpg') ? 'jpeg' : end($arrImg);
        $imgFunc = "imagecreatefrom" . $format; //определение функции для расширения файла
        // получаем формат маски
        //$arrMask = explode(".", $mask);
        //$format = (end($arrMask) == 'jpg') ? 'jpeg': end($arrMask);
        $maskFunc = "imagecreatefrompng"; //определение функции для расширения файла

        $image = $imgFunc($image); // загружаем картинку
        $mask = $maskFunc($mask); // загружаем маску
        $width = imagesx($image); // определяем ширину картинки
        $height = imagesy($image); // определяем высоту картинки
        $img = imagecreatetruecolor($width, $height); // создаем холст для будущей картинки
        //$transColor = imagecolorallocate($img, 0, 0, 0); // определяем прозрачный цвет для картинки. Черный
        //imagecolortransparent($img,$transColor); // задаем прозрачность для картинки
        // перебираем картинку по пикселю
        imagealphablending($img, true);
        for ($posX = 0; $posX < $width; $posX++) {
            for ($posY = 0; $posY < $height; $posY++) {
                $colorIndex = imagecolorat($image, $posX, $posY); // получаем индекс цвета пикселя в координате $posX, $posY для картинки
                $colorImage = imagecolorsforindex($image, $colorIndex); // получаем цвет по его индексу в формате RGB
                $colorIndex = imagecolorat($mask, $posX, $posY); // получаем индекс цвета пикселя в координате $posX, $posY для маски
                $maskColor = imagecolorsforindex($mask, $colorIndex); // получаем цвет по его индексу в формате RGB
                // если в точке $posX, $posY цвет маски не белый, то наносим на холст пиксель с нужным цветом
                if ($maskColor['red'] == 0 && $maskColor['green'] == 0 && $maskColor['blue'] == 0) {
                    $colorIndex = imagecolorallocate($img, $colorImage['red'], $colorImage['green'], $colorImage['blue']); // получаем цвет для пикселя
                    imagesetpixel($img, $posX, $posY, $colorIndex); // рисуем пиксель
                } else {
                    $colorIndex = imagecolorallocate($img, $colorImage['red'], $colorImage['green'], $colorImage['blue']); // получаем цвет для пикселя
                    imagesetpixel($img, $posX, $posY, $colorIndex); // рисуем пиксель
                    $colorIndex = imagecolorallocatealpha($img, 255, 0, 0, 127 - round((($maskColor['red'] - 1) / 2) * 0.7, 0));
                    imagesetpixel($img, $posX, $posY, $colorIndex);
                }

                /*if (!($maskColor['red'] == 255 && $maskColor['green'] == 255 && $maskColor['blue'] == 255)){
                    $colorIndex = imagecolorallocate($img, $colorImage['red'], $colorImage['green'], $colorImage['blue']); // получаем цвет для пикселя
                    imagesetpixel($img, $posX, $posY, $colorIndex); // рисуем пиксель
                } else {
                    $colorIndex = imagecolorallocate($img, $colorImage['red'], $colorImage['green'], $colorImage['blue']); // получаем цвет для пикселя
                    imagesetpixel($img, $posX, $posY, $colorIndex); // рисуем пиксель
                    $colorIndex = imagecolorallocatealpha( $img, 255, 0, 0, 90 );
                    imagesetpixel($img, $posX, $posY, $colorIndex);
                }*/
            }
        }
        return $img; // вернем изображение
    }

    public function getAvailablePredict($id, $base)
    {
        $result = $this->send('POST', '/WebMarker/getAvailablePredict', ["body" => json_encode(["task_id" => $id])]);

        if ($result['status'] == 'Done' && !empty($result["result"])) {
            $img = "data:image/png;base64, " . $result["result"][0];
            $result = ["status" => "complete"];
            $img = $this->imagemask($base, $img);
            ob_start();
            imagepng($img);
            $imageData = ob_get_clean();
            $result["img"] = "data:image/png;base64, " . base64_encode($imageData);
        } else {
            $result = ["status" => "wait"];
        }

        return $result;
    }

    private function getBase64Image($src)
    {

        $extension = strtolower(pathinfo($src, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'gif':
                $image = imagecreatefromgif($src);
                break;
            case 'png':
                $image = imagecreatefrompng($src);
                break;
            default:
                $image = imagecreatefromjpeg($src);
                break;
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        return base64_encode($imageData);
    }

}
