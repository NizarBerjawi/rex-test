<?php

namespace Domain\Contact\Models;

use Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends BaseModel
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
    ];

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }
}
