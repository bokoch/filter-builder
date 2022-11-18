<?php

namespace Mykolab\FilterBuilder\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestModel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
