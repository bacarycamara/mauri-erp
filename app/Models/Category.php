<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'image',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position'  => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('position');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_name . ' > ' . $this->name;
        }

        return $this->name;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return asset('images/default-category.png');
    }

    /*
    |--------------------------------------------------------------------------
    | ERP LOGIQUE AUTOMATIQUE
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        /*
        |--------------------------------------------------------------------------
        | CREATING
        |--------------------------------------------------------------------------
        */
        static::creating(function ($category) {

            // Slug automatique unique
            $slug = Str::slug($category->name);
            $originalSlug = $slug;
            $count = 1;

            while (static::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $category->slug = $slug;

            // Position automatique
            if ($category->position === null) {

                $maxPosition = static::where('parent_id', $category->parent_id)
                    ->max('position');

                $category->position = $maxPosition !== null
                    ? $maxPosition + 1
                    : 0;
            }

            Cache::forget('categories');
            Cache::forget('categories.tree');
        });

        /*
        |--------------------------------------------------------------------------
        | UPDATING
        |--------------------------------------------------------------------------
        */
        static::updating(function ($category) {

            if ($category->isDirty('name')) {

                $slug = Str::slug($category->name);
                $originalSlug = $slug;
                $count = 1;

                while (
                    static::where('slug', $slug)
                        ->where('id', '!=', $category->id)
                        ->exists()
                ) {
                    $slug = $originalSlug . '-' . $count++;
                }

                $category->slug = $slug;
            }

            if ($category->parent_id == $category->id) {
                $category->parent_id = null;
            }

            Cache::forget('categories');
            Cache::forget('categories.tree');
        });

        /*
        |--------------------------------------------------------------------------
        | DELETING
        |--------------------------------------------------------------------------
        */
        static::deleting(function ($category) {

            if ($category->children()->exists()) {
                throw new \Exception(
                    "Impossible de supprimer : cette catégorie possède des sous-catégories."
                );
            }

            Cache::forget('categories');
            Cache::forget('categories.tree');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HIERARCHIE HELPERS
    |--------------------------------------------------------------------------
    */

    public function hasChildren()
    {
        return $this->children()->exists();
    }

    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    public function level()
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPERS
    |--------------------------------------------------------------------------
    */

    public static function tree()
    {
        return static::with('children')
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE HELPERS (ERP PERFORMANCE)
    |--------------------------------------------------------------------------
    */

    public static function cached()
    {
        return Cache::rememberForever('categories', function () {
            return static::active()
                ->ordered()
                ->get();
        });
    }

    public static function cachedTree()
    {
        return Cache::rememberForever('categories.tree', function () {
            return static::with('children')
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get();
        });
    }
}