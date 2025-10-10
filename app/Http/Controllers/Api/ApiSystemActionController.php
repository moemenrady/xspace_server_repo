<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SystemActionResource;
use App\Models\SystemAction;
use Illuminate\Http\Request;

class ApiSystemActionController extends Controller
{
    public function index(Request $request)
    {
        // validation (light) - note: action can be array or string, so keep it nullable (we'll normalize later)
        $request->validate([
            'q' => 'nullable|string|max:500',
            'action' => 'nullable', // accept string or array (we'll normalize)
            'user_id' => 'nullable|numeric',
            'invoice_id' => 'nullable|numeric',
            'shift_id' => 'nullable|numeric',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'amount_min' => 'nullable|numeric',
            'amount_max' => 'nullable|numeric',
            'source' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:200',
            'sort_by' => 'nullable|string',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);

        $q = $request->input('q');
        $rawAction = $request->input('action'); // might be array or string or null
        $userId = $request->input('user_id');
        $invoiceId = $request->input('invoice_id');
        $shiftId = $request->input('shift_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $amountMin = $request->input('amount_min');
        $amountMax = $request->input('amount_max');
        $source = $request->input('source');
        $perPage = (int)$request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = SystemAction::query()->with('user');

        // ===== normalize action filter into an array of strings (support: array, csv, single string)
        $actions = [];
        if ($rawAction !== null && $rawAction !== '') {
            if (is_array($rawAction)) {
                // example: action[]=login&action[]=logout
                $actions = collect($rawAction)->flatMap(function ($v) {
                    if (is_string($v) && str_contains($v, ',')) {
                        return explode(',', $v);
                    }
                    return [$v];
                })->filter()->map(fn($x)=> trim($x))->unique()->values()->all();
            } elseif (is_string($rawAction)) {
                // could be "login,logout" or single "login"
                if (str_contains($rawAction, ',')) {
                    $actions = collect(explode(',', $rawAction))->map(fn($x)=> trim($x))->filter()->unique()->values()->all();
                } else {
                    $actions = [trim($rawAction)];
                }
            }
        }

        // basic filters
        if (!empty($actions)) {
            // use whereIn so multiple selected actions are matched
            $query->whereIn('action', $actions);
        }

        if ($userId) $query->where('user_id', $userId);
        if ($invoiceId) $query->where('invoice_id', $invoiceId);
        if ($shiftId) $query->where('shift_id', $shiftId);
        if ($source) $query->where('source', $source);

        if ($amountMin !== null) $query->where('amount', '>=', $amountMin);
        if ($amountMax !== null) $query->where('amount', '<=', $amountMax);

        // date range
        if ($dateFrom || $dateTo) {
            $query->where(function($qb) use ($dateFrom, $dateTo) {
                if ($dateFrom) $qb->where('created_at', '>=', $dateFrom);
                if ($dateTo) $qb->where('created_at', '<=', $dateTo.' 23:59:59');
            });
        }

        // smart free-text search across many fields (uses model scope)
        if ($q) {
            $query->smartSearch($q);
        }

        // sorting white-list to prevent SQL injection: only allow certain columns
        $allowedSort = ['id','created_at','amount','action','user_id'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';

        $query->orderBy($sortBy, $sortDir);

        $page = $query->paginate($perPage)->appends($request->query());

        // return paginated resource
        return SystemActionResource::collection($page)
            ->additional(['meta' => [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ]]);
    }
}
