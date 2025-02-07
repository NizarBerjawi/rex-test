<?php

namespace Domain\Contact\Models;

use Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends BaseModel
{
    protected $fillable = [
        'phone',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
