<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanModel extends Model
{
    protected $table = 'library.loan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'loaned_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberModel::class, 'member_id');
    }

    public function loanedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'loaned_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItemModel::class, 'loan_id');
    }
}