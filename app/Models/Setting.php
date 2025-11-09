<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Helper: get setting value by key with optional default
     */
    public static function getValue(string $key, $default = null)
    {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }

    /**
     * Helper: set setting value
     */
    public static function setValue(string $key, $value, ?string $description = null)
    {
        return static::updateOrCreate(['key' => $key], [
            'value' => $value,
            'description' => $description,
        ]);
    }
}
