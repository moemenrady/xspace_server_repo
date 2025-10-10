<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\SystemActionType;

class SystemAction extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'actionable_type',
        'actionable_id',
        'invoice_id',
        'shift_id',
        'amount',
        'note',
        'meta',
        'ip',
        'source',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    // علاقة بالمستخدم
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // polymorphic relation لأي شيء
    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Shift::class);
    }

    // Helper: create log easily
    public static function log(array $data): self
    {
        // توقع أن $data يحتوي على keys: user_id, action (SystemActionType|string), actionable (Model|null),
        // invoice_id, amount, note, meta (array), ip, source, shift_id

        if (isset($data['action']) && $data['action'] instanceof SystemActionType) {
            $data['action'] = $data['action']->value;
        }

        // actionable model support: ['actionable' => $modelInstance]
        if (!empty($data['actionable']) && is_object($data['actionable'])) {
            $model = $data['actionable'];
            $data['actionable_type'] = get_class($model);
            $data['actionable_id'] = $model->getKey();
            unset($data['actionable']);
        }

        return self::create($data);
    }
     // scope: flexible search across many fields
    public function scopeSmartSearch($query, ?string $q)
    {
        if (!strlen((string)$q)) return $query;

        $q = trim($q);
        $query->where(function($qb) use ($q) {
            $qb->where('id', $q)
               ->orWhere('action', 'like', "%{$q}%")
               ->orWhere('ip', 'like', "%{$q}%")
               ->orWhere('source', 'like', "%{$q}%")
               ->orWhere('note', 'like', "%{$q}%")
               ->orWhere('amount', $q)
               // search in JSON meta (simple string search)
               ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(meta, '$'))) LIKE ?", ["%".strtolower($q)."%"])
               // search user name
               ->orWhereHas('user', function($u) use ($q) {
                    $u->whereRaw('LOWER(name) like ?', ['%'.strtolower($q).'%']);
               });
        });

        return $query;
    }
     public function scopeDateRange($query, $from, $to)
    {
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to.' 23:59:59');
        return $query;
    }
}
