<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Dataset;
use App\Models\Label;
use App\Models\Polygon;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();
        $user_id = User::first()->id;
        $project = Project::create([
            'id' => 16,
            'name' => 'pr1',
            'user_id' => $user_id,
            'description' => 'description'
        ]);
        $dataset = Dataset::create([
            'user_id' => $user_id,
            'project_id' => $project->id,
            'name' => 'dtst1',
            'status' => 'start',
            'progress' => '0',
            'link_id' => '/',
        ]);
        $label = Label::create([
            'user_id' => $user_id,
            'project_id' => $project->id,
            'name' => 'ball',
            'color' => 'red'
        ]);
        $media = $dataset->addMedia(storage_path('dataset/15.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_15.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/16.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_16.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/17.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_17.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/18.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_18.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/19.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_19.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/20.jfif'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_20.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/21.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_21.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/61.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_61.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/62.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_62.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/63.jfif'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_63.json')),
            'image_id'=>$media->id
        ]);
        $media = $dataset->addMedia(storage_path('dataset/64.jpg'))->toMediaCollection('ball');
        Polygon::create([
            'dataset_id'=>$dataset->id,
            'data'=>file_get_contents(storage_path('dataset/data_64.json')),
            'image_id'=>$media->id
        ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
