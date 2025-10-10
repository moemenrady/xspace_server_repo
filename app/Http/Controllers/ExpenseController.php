<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseDraft;
use App\Models\ExpenseType;
use App\Models\Shift;
use App\Models\ShiftAction;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
  // صفحة إضافة مصروف
  public function create()
  {
    $types = ExpenseType::all(); // كل أنواع المصاريف
    return view('expense.admin.create', compact('types'));
  }


  public function store(Request $request)
  {
    $request->validate([
      'expense_type_id' => 'required|exists:expense_types,id',
      'amount' => 'required|numeric|min:0',
      'note' => 'nullable|string|max:500',
    ]);

    try {
      $user = Auth::user();

      $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

      $expense = Expense::create([
        'expense_type_id' => $request->expense_type_id,
        'amount' => $request->amount,
        'note' => $request->note,
        'added_by' => auth()->id(),
      ]);

      if ($openShift ) {
        $expenseAmount = $expense->amount ?? $request->input('amount') ?? 0;
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'expense_note',
          'invoice_id' => null, // ضمان عدم ربط فاتورة
          'amount' => 0, // هذه عملية مصروف لذا الإيراد = 0
          'expense_amount' => $expenseAmount ?: null,
          'notes' => $request->note,
        ]);

        // 4) تحديث إجمالي المصروف في الشيفت
        if ($expenseAmount && $expenseAmount > 0) {
          $openShift->total_expense = $openShift->total_expense + $expenseAmount;
          $openShift->save();
        }
      }

      return redirect()->route("main.create")->with('success', 'تم إضافة المصروف بنجاح ✅');
    } catch (Exception $e) {
      return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة المصروف ❌');
    }
  }

  public function index()
  {
    $expenses = Expense::with('type', 'admin')->latest()->paginate(10);
    return view('expenses.index', compact('expenses'));
  }

  public function convertFromDraft(Request $request, ExpenseDraft $draft)
  {
    $request->validate([
      'expense_type_id' => 'required|exists:expense_types,id',
      'amount' => 'required|numeric|min:0',
    ]);

    // إنشاء مصروف رسمي
    try {
      Expense::create([
        'expense_type_id' => $request->expense_type_id,
        'amount' => $request->amount,
        'note' => $draft->note,
        'added_by' => auth()->id(),
      ]);

      // بعد التحويل نقدر نمسح أو نأرشف الدرفت
      $draft->delete();

      return redirect()->back()->with('success', 'تم تحويل الملاحظة إلى مصروف رسمي ✅');

    } catch (Exception $e) {
      return redirect()->back()->with('error', 'حدث خطأ ما ❌');

    }
  }
}
