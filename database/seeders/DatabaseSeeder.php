<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create categories using factory - Category::factory() method ကိုအသုံးပြုပြီး ဖန်တီးမယ်
        Category::factory(10)->create();
        Product::factory(50)->create();

        // Create each category individually - Category::create() method ကိုအသုံးပြုပြီး တစ်ခုချင်းစီဖန်တီးမယ်
        // Category::create([
        //     'name' => 'Home Appliances',
        //     'description' => 'Appliances for home use',
        // ]);

        // Category::create([
        //     'name' => 'Battery',
        //     'description' => 'Electronic devices and gadgets',
        // ]);

        // Category::create([
        //     'name' => 'Books',
        //     'description' => 'All kinds of books',
        // ]);

        // Create multiple categories at once - Category::insert() method ကိုအသုံးပြုပြီး တစ်ပြိုင်တည်းဖန်တီးမယ်
        // Category::insert([
        //     ['name' => 'Home ', 'description' => 'Appliances for home use'],
        //     ['name' => 'Bird', 'description' => 'Electronic devices and gadgets'],
        //     ['name' => 'Keyboards', 'description' => 'All kinds of books'],
        // ]);

        // if want to create separate seeders
        // Product::factory()->count(50)->create();
        // php artisan make:seeder CategorySeeder
        //
        // php artisan make:seeder ProductSeeder
        // $this->call([
        //     CategorySeeder::class,
        //     ProductSeeder::class,
        // ]);
        // then run php artisan db:seed to seed the database

    }
}
