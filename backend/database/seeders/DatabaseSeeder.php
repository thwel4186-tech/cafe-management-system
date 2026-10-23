<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Local demo accounts and menu. Don't seed these in a public deployment.
        $this->call(CafeSeeder::class);
    }
}
