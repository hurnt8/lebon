<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdPhoto;
use App\Models\Camera;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CameraAdSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $catalogue = [
        ['brand' => 'Sony', 'product' => 'Appareil photos', 'type' => 'Appareil photo hybride', 'universe' => 'Appareil photo', 'title' => 'Sony Alpha 7 IV (A7 IV)', 'color' => 'Noir', 'condition' => 'Très bon état', 'price' => 1500, 'rgb' => [30, 30, 35]],
        ['brand' => 'Canon', 'product' => 'Appareil photos', 'type' => 'Appareil photo reflex', 'universe' => 'Appareil photo', 'title' => 'Canon EOS 5D Mark IV', 'color' => 'Noir', 'condition' => 'Bon état', 'price' => 1200, 'rgb' => [20, 20, 20]],
        ['brand' => 'Fujifilm', 'product' => 'Appareil photos', 'type' => 'Appareil photo hybride', 'universe' => 'Appareil photo', 'title' => 'Fujifilm X-T5', 'color' => 'Argent', 'condition' => 'Comme neuf', 'price' => 1450, 'rgb' => [150, 150, 145]],
        ['brand' => 'Nikon', 'product' => 'Appareil photos', 'type' => 'Appareil photo reflex', 'universe' => 'Appareil photo', 'title' => 'Nikon D850', 'color' => 'Noir', 'condition' => 'Bon état', 'price' => 1690, 'rgb' => [25, 25, 30]],
    ];

    public function run(): void
    {
        $seller = Seller::where('pseudo', 'TechDeals_Nova')->first();

        if (!$seller) {
            return;
        }

        foreach ($this->catalogue as $camera) {
            $ad = Ad::create([
                'seller_id'    => $seller->id,
                'category'     => 'camera',
                'title'        => $camera['title'],
                'description'  => "Je vends mon {$camera['title']}, {$camera['condition']}, coloris {$camera['color']}.",
                'price'        => $camera['price'],
                'city'         => $seller->city,
                'region'       => null,
                'department'   => null,
                'postal_code'  => null,
                'status'       => 'active',
                'published_at' => now()->subDays(rand(0, 15)),
                'share_token'  => Str::random(10),
                'views'        => rand(5, 300),
            ]);

            Camera::create([
                'ad_id'     => $ad->id,
                'condition' => $camera['condition'],
                'color'     => $camera['color'],
                'universe'  => $camera['universe'],
                'product'   => $camera['product'],
                'type'      => $camera['type'],
                'brand'     => $camera['brand'],
            ]);

            $ad->bankAccount()->create([
                'seller_id'           => $seller->id,
                'iban'                => 'FR76' . collect(range(1, 20))->map(fn () => random_int(0, 9))->implode(''),
                'bic'                 => 'BNPAFRPPXXX',
                'account_holder_name' => 'LEBONCOIN',
                'is_default'          => false,
            ]);

            foreach (range(0, rand(0, 2)) as $i) {
                $data     = $this->generatePlaceholderImage($camera['title'], $camera['rgb']);
                $filename = "annonces/{$ad->id}/photos/seed-" . Str::random(8) . '.jpg';

                Storage::disk('public')->put($filename, $data);

                AdPhoto::create([
                    'ad_id'         => $ad->id,
                    'disk'          => 'public',
                    'path'          => $filename,
                    'original_name' => Str::slug($camera['title']) . "-{$i}.jpg",
                    'mime_type'     => 'image/jpeg',
                    'size'          => strlen($data),
                    'order'         => $i,
                ]);
            }
        }
    }

    private function generatePlaceholderImage(string $label, array $rgb): string
    {
        $width  = 800;
        $height = 600;

        $image = imagecreatetruecolor($width, $height);
        $bg    = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($image, 0, 0, $bg);

        $white = imagecolorallocate($image, 255, 255, 255);
        $font  = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $x = (int) (($width - $textWidth) / 2);
        $y = (int) ($height / 2);
        imagestring($image, $font, $x, $y, $label, $white);

        ob_start();
        imagejpeg($image, null, 85);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }
}
