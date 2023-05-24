<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('languages')->insert([
            ['prefix' => 'ua', 'locale' => 'Ukrainian'],
            ['prefix' => 'ru', 'locale' => 'Russian'],
            ['prefix' => 'en', 'locale' => 'English'],
        ]);
    }
}
