<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /** @var array $seeders */
    protected $seeders = [];

    public function __construct() {
        $this->getProjectSeeders();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call($this->seeders);
    }

    protected function getProjectSeeders() {
        $projectSeedersFolder = base_path('packages/project/src/Project/Seeders');
        if (file_exists($projectSeedersFolder)) {
            // Scan all *Seeder.php files and include it as routes
            foreach (scandir($projectSeedersFolder) as $filename) {
                $path = $projectSeedersFolder . '/' . $filename;

                if (!preg_match('/[a-zA-Z0-9]+Seeder\.php$/', $filename)) continue;

                if (is_file($path)) {
                    $this->seeders[] = 'Project\\Project\\Seeders\\' . basename($path, '.php');
                }
            }
        }
    }
}
