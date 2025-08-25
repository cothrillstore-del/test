<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use App\Models\ReviewMedia;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        // Create test users if not exists
        $users = User::take(5)->get();
        if ($users->count() < 5) {
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'name' => "Test User $i",
                    'email' => "user$i@test.com",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
            }
            $users = User::take(5)->get();
        }

        $products = Product::all();
        
        $prosOptions = [
            'Great distance',
            'Excellent feel',
            'Forgiving on mishits',
            'Beautiful design',
            'Good value for money',
            'Improved accuracy',
            'Easy to hit',
            'Consistent performance'
        ];

        $consOptions = [
            'Expensive',
            'Not for beginners',
            'Limited adjustability',
            'Heavy',
            'Takes time to get used to',
            'Customer service issues'
        ];

        foreach ($products as $product) {
            // Create 3-5 reviews per product
            $reviewCount = rand(3, 5);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                $rating = rand(3, 5);
                $status = ['pending', 'approved', 'approved', 'approved'][rand(0, 3)];
                
                $review = Review::create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                    'title' => $this->generateTitle($rating),
                    'content' => $this->generateContent($rating),
                    'rating' => $rating,
                    'pros' => array_rand(array_flip($prosOptions), rand(2, 4)),
                    'cons' => array_rand(array_flip($consOptions), rand(1, 3)),
                    'verified_purchase' => rand(0, 1),
                    'helpful_count' => rand(0, 50),
                    'unhelpful_count' => rand(0, 10),
                    'skill_level' => ['beginner', 'intermediate', 'advanced'][rand(0, 2)],
                    'status' => $status,
                    'approved_by' => $status === 'approved' ? 1 : null,
                    'approved_at' => $status === 'approved' ? now()->subDays(rand(1, 30)) : null,
                    'is_featured' => rand(0, 100) > 90,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);
            }
        }
    }

    private function generateTitle($rating)
    {
        $goodTitles = [
            'Excellent product, highly recommend!',
            'Great addition to my bag',
            'Worth every penny',
            'Game changer for my golf',
            'Fantastic quality and performance'
        ];

        $averageTitles = [
            'Good product with some flaws',
            'Decent but not perfect',
            'Mixed feelings about this',
            'Good for the price',
            'Solid option for most golfers'
        ];

        return $rating >= 4 ? $goodTitles[array_rand($goodTitles)] : $averageTitles[array_rand($averageTitles)];
    }

    private function generateContent($rating)
    {
        $templates = [
            "I've been using this product for several weeks now and I must say I'm impressed. The build quality is excellent and it performs exactly as advertised. ",
            "After extensive testing on the course, I can confidently say this is a solid choice. ",
            "As someone who plays golf regularly, I appreciate the attention to detail in this product. ",
            "This product has definitely improved my game. The technology really makes a difference. ",
            "I was skeptical at first, but after giving it a try, I'm convinced. "
        ];

        $content = $templates[array_rand($templates)];
        
        if ($rating >= 4) {
            $content .= "The performance exceeded my expectations and I would definitely recommend it to other golfers. ";
        } else {
            $content .= "While there are some areas for improvement, overall it's a decent product for the price point. ";
        }

        $content .= "The feel and feedback are exactly what I was looking for. Build quality seems solid and should last for years to come.";

        return $content;
    }
}