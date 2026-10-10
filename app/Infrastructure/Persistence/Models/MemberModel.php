<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberModel extends Model
{
    protected $table = 'library.member';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function loans(): HasMany
    {
        return $this->hasMany(LoanModel::class, 'member_id');
    }
}