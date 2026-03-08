<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
       DB::table('categories')->delete();

        $categories = [

            // ===== ELECTRONIQUE =====
            'Électronique' => [
                'Téléphones',
                'Accessoires téléphones',
                'Tablettes',
                'Télévisions',
                'Audio & Casques'
            ],

            // ===== INFORMATIQUE =====
            'Informatique' => [
                'Ordinateurs portables',
                'Ordinateurs de bureau',
                'Imprimantes',
                'Claviers & Souris',
                'Disques durs'
            ],

            // ===== FOURNITURES =====
            'Fournitures de bureau' => [
                'Stylos',
                'Cahiers',
                'Classeurs',
                'Agendas',
                'Ramettes papier'
            ],

            // ===== SCOLAIRE =====
            'Matériel scolaire' => [
                'Sacs scolaires',
                'Boîtes mathématiques',
                'Ardoises',
                'Livres scolaires',
                'Uniformes'
            ],

            // ===== ALIMENTATION =====
            'Alimentation' => [
                'Boissons',
                'Conserves',
                'Snacks',
                'Produits laitiers',
                'Céréales'
            ],

            // ===== MAURITANIE BUSINESS =====
            'Commerce général' => [
                'Produits importés',
                'Produits locaux',
                'Quincaillerie',
                'Matériel bâtiment',
                'Pièces automobiles'
            ],
        ];

        $position = 1;

        foreach ($categories as $parentName => $children) {

            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_id' => null,
                'description' => 'Catégorie principale ' . $parentName,
                'image' => null,
                'position' => $position++,
                'is_active' => 1,
            ]);

            foreach ($children as $index => $childName) {

                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName . '-' . $parentName),
                    'parent_id' => $parent->id,
                    'description' => 'Sous-catégorie ' . $childName,
                    'image' => null,
                    'position' => $index + 1,
                    'is_active' => 1,
                ]);
            }
        }
    }
}