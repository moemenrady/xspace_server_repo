<?php
// app/Http/Requests/StoreInvoiceRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array 
    {
        return [
            'client_id' => ['nullable','integer','exists:clients,id'],

            // لازم يكون فيه items
            'items' => ['required','array','min:1'],

            // النوع الأساسي
            'items.*.item_type' => ['required','in:product,subscription,booking,session,deposit'],

            // الكمية (معظم الحالات)
            'items.*.qty' => ['nullable','integer','min:1'],

            // product
            'items.*.product_id' => [
                'required_if:items.*.item_type,product',
                'integer',
                'exists:products,id'
            ],

            // subscription
            'items.*.subscription_id' => [
                'required_if:items.*.item_type,subscription',
                'integer',
                'exists:subscriptions,id'
            ],

            // booking
            'items.*.booking_id' => [
                'required_if:items.*.item_type,booking',
                'integer',
                'exists:bookings,id'
            ],

            // session
            'items.*.session_id' => [
                'required_if:items.*.item_type,session',
                'integer',
                'exists:sessions,id'
            ],

            // deposit
            'items.*.amount' => [
                'required_if:items.*.item_type,deposit',
                'numeric',
                'min:0.01'
            ],
            'items.*.description' => ['nullable','string','max:255'],

            // ملاحظات عامة على الفاتورة
            'notes' => ['nullable','string']
        ];
    }
}
