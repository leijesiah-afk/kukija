<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@kukija.test'],
            [
                'first_name' => 'Kukija',
                'last_name' => 'Admin',
                'name' => 'Kukija Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@kukija.test'],
            [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        $categories = [
            ['name' => 'Best', 'slug' => 'best'],
            ['name' => 'New', 'slug' => 'new'],
            ['name' => 'Fun', 'slug' => 'fun'],
            ['name' => 'Yum', 'slug' => 'yum'],
            ['name' => 'Combo', 'slug' => 'combo'],
            ['name' => 'Cute', 'slug' => 'cute'],
            ['name' => 'Bold', 'slug' => 'bold'],
            ['name' => 'Fancy', 'slug' => 'fancy'],
            ['name' => 'Zen', 'slug' => 'zen'],
            ['name' => 'Sweet', 'slug' => 'sweet'],
            ['name' => 'Messy', 'slug' => 'messy'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'is_active' => true,
                ]
            );
        }

        $products = [
            [
                'name' => 'Lost & Round',
                'description' => 'Classic ring-shaped cookie with a sweet glaze',
                'price' => 100.00,
                'image_path' => 'imgs/ring.png',
                'category' => 'best',
                'sticker' => 'BEST',
            ],
            [
                'name' => 'Alaskrumbs',
                'description' => 'Creamy milk cookie with a soft, melt-in-your-mouth texture',
                'price' => 80.00,
                'image_path' => 'imgs/milky_alaska_cookie.png',
                'category' => 'new',
                'sticker' => 'NEW',
            ],
            [
                'name' => 'Sharkookie',
                'description' => 'Fun shark-shaped cookie perfect for ocean lovers',
                'price' => 120.00,
                'image_path' => 'imgs/sharkie.png',
                'category' => 'fun',
                'sticker' => 'FUN',
            ],
            [
                'name' => 'Berry Cookie',
                'description' => 'Fresh strawberry flavor with real fruit bits',
                'price' => 125.00,
                'image_path' => 'imgs/strawberry.png',
                'category' => 'yum',
                'sticker' => 'YUM',
            ],
            [
                'name' => '5 Cookies Comes with Cup of Milk',
                'description' => 'Perfect combo pack with 5 assorted cookies and fresh milk',
                'price' => 250.00,
                'image_path' => 'imgs/5_cookies_comes_with_cup_of_milk.png',
                'category' => 'combo',
            ],
            [
                'name' => 'Babe Bites',
                'description' => 'Cute mini cookies that are irresistibly delicious',
                'price' => 95.00,
                'image_path' => 'imgs/babe_bites.png',
                'category' => 'cute',
            ],
            [
                'name' => 'Big Black Cookie',
                'description' => 'Rich dark chocolate cookie for the bold at heart',
                'price' => 150.00,
                'image_path' => 'imgs/big_black_cookie.png',
                'category' => 'bold',
            ],
            [
                'name' => 'Doughquette',
                'description' => 'Elegant French-inspired cookie with delicate flavor',
                'price' => 110.00,
                'image_path' => 'imgs/doughquette.png',
                'category' => 'fancy',
            ],
            [
                'name' => "Maynard's Matcha",
                'description' => 'Premium matcha green tea cookie with authentic taste',
                'price' => 135.00,
                'image_path' => 'imgs/maynard\'s_matcha.png',
                'category' => 'zen',
            ],
            [
                'name' => 'Nutella',
                'description' => 'Loaded with creamy Nutella hazelnut spread',
                'price' => 140.00,
                'image_path' => 'imgs/nutella.png',
                'category' => 'sweet',
            ],
            [
                'name' => 'U Might Need a Bib for Deez',
                'description' => 'Messy, gooey, and absolutely worth it!',
                'price' => 160.00,
                'image_path' => 'imgs/u_might_need_a_bib_for_deez.png',
                'category' => 'messy',
            ],
        ];

        foreach ($products as $p) {
            $category = Category::query()->where('slug', $p['category'])->first();
            if (! $category) {
                continue;
            }

            $slug = Str::slug($p['name']);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $p['name'],
                    'description' => $p['description'] ?? null,
                    'price' => $p['price'],
                    'stock' => 100,
                    'image_path' => $p['image_path'] ?? null,
                    'sticker' => $p['sticker'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
