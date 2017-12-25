<?php

namespace Kommercio\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ReFlatIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reflatindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reflatindexing...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $flattenModelNames = [];

        // Get all Models & only filter those that use `FlatIndexable` trait
        $dir = app_path('Models');
        $files = File::allFiles($dir);

        foreach ($files as $file)
        {
            $className = '\Kommercio\Models\\'.str_replace('.php', '', str_replace('/', '\\', $file->getRelativePathname()));
            $traits = class_uses($className);

            if(in_array('Kommercio\Traits\Model\FlatIndexable', $traits)){
                $flattenModelNames[] = $className;
            }
        }

        $flattenModelName = $this->choice('Choose what Model you want to re-index', $flattenModelNames);

        $flattenModel = with(new $flattenModelName());

        if (!$flattenModel->isFlatIndexable()) {
            $this->error($flattenModelName . ' doesn\'t have index table');
        }

        // Wipe old index
        DB::table($flattenModel->getFlatTable())->truncate();
        $this->info('Table ' . $flattenModel->getFlatTable() . ' truncated');

        $modelIds = $flattenModelName::pluck('id');
        $bar = $this->output->createProgressBar($modelIds->count());

        foreach($modelIds as $modelId){
            $model = $flattenModelName::find($modelId);

            if ($model) {
                $model->saveFlatIndex();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info(' ' . $flattenModelName . ' is successfully re-flat-indexed...');
    }
}
