<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PermanenceSousOfficier extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'permanence_sous_officier';

    protected $fillable = [
        'permanence_id',
        'sous_officier_id',
        'site_id',
    ];

    // ========== RELATIONS ==========

    public function permanence(): BelongsTo
    {
        return $this->belongsTo(Permanence::class);
    }

    public function sousOfficier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sous_officier_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // ========== ACTIVITY LOG ==========

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permanence_id', 'sous_officier_id', 'site_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
