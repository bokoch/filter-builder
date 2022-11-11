<?php

namespace Mykolab\FilterBuilder\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelatedModel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function testModels(): HasMany
    {
        return $this->hasMany(TestModel::class);
    }
}