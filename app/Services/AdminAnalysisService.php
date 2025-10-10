<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalysisService
{
  public function build(string $from, string $to): array
  {
    $fromDt = Carbon::parse($from)->startOfDay();
    $toDt = Carbon::parse($to)->endOfDay();
    // 1) إجمالي الإيرادات
    $revenue = (float) Invoice::whereBetween('created_at', [$fromDt, $toDt])->sum('total');

    // 2) COGS + Gross Profit
    $itemsAgg = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
      ->whereBetween('invoices.created_at', [$fromDt, $toDt])
      ->selectRaw('SUM(invoice_items.qty * invoice_items.cost) as cogs')
      ->selectRaw('SUM(invoice_items.qty * (invoice_items.price - invoice_items.cost)) as gross_profit')
      ->first();

    $cogs = (float) $itemsAgg->cogs;
    $grossProfit = (float) $itemsAgg->gross_profit;

    // 3) مصروفات مجمعة بالأنواع
    $expensesByType = Expense::with('type:id,name')
      ->whereBetween('created_at', [$fromDt, $toDt])
      ->select('expense_type_id', DB::raw('SUM(amount) as total'))
      ->groupBy('expense_type_id')
      ->get();

    $totalExpenses = $expensesByType->sum('total');

    // 4) صافي الربح
    $netProfit = $grossProfit - $totalExpenses;

    return [
      'revenue' => $revenue,
      'cogs' => $cogs,
      'gross_profit' => $grossProfit,
      'expenses' => $totalExpenses,
      'net_profit' => $netProfit,
      'expenses_by_type' => $expensesByType->map(function ($row) {
        return [
          'type' => $row->type->name ?? 'غير معروف',
          'amount' => (float) $row->total,
        ];
      }),
    ];
  }
}
