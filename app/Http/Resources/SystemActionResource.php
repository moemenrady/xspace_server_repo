<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SystemActionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => \Str::title(str_replace('_', ' ', $this->action)),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                ];
            }),
            'amount' => $this->amount,
            'note' => $this->note,
            'meta' => $this->meta,
            'ip' => $this->ip,
            'source' => $this->source,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'actionable' => $this->when($this->relationLoaded('actionable') && $this->actionable, function () {
                return [
                    'type' => class_basename($this->actionable_type),
                    'id' => $this->actionable_id,
                ];
            }),
        ];
    }
}
