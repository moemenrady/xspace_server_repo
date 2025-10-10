<!-- Split Session Modal -->
<div id="splitModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeSplitModal()">&times;</span>
        <h2>🔀 إنشاء حساب منفصل</h2>

        <form action="{{ route('sessions.splitCheckout', $session->id) }}" method="POST">
            @csrf

            <!-- عدد الأشخاص -->
            <label for="persons">عدد الأشخاص:</label>
            <input type="number" name="persons" min="1" max="{{ $session->persons - 1 }}" value="1">

            <!-- اختيار المشتريات -->
            <h4>اختر المشتريات لهذا الحساب:</h4>
            <div class="products-list-modal">
                @foreach ($purchases as $purchase)
                    <label class="product-option">
                        <input type="checkbox" name="products[{{ $purchase->id }}]" value="{{ $purchase->quantity }}">
                        {{ $purchase->product->name }} × {{ $purchase->quantity }}
                    </label>
                @endforeach
            </div>

            <!-- الأزرار -->
            <div class="modal-actions">
                <button type="submit" class="confirm-btn">إنهاء منفصل</button>
                <button type="button" class="cancel-btn" onclick="closeSplitModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSplitModal() {
        document.getElementById("splitModal").style.display = "flex";
    }
    function closeSplitModal() {
        document.getElementById("splitModal").style.display = "none";
    }
</script>
<style>
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* محتوى المودال */
.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    position: relative;
    animation: fadeIn 0.3s ease-in-out;
}

/* زرار الإغلاق */
.close-btn {
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 22px;
    cursor: pointer;
    color: #666;
}

.close-btn:hover {
    color: #000;
}

/* حقول الإدخال */
.modal-content input[type="number"] {
    width: 100%;
    padding: 8px;
    margin: 10px 0 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
}

/* الأزرار */
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.confirm-btn {
    background: #f05a4f;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.confirm-btn:hover {
    background: #d9443c;
}

.cancel-btn {
    background: #eee;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
}

.cancel-btn:hover {
    background: #ddd;
}

/* حركة دخول المودال */
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.9);}
    to {opacity: 1; transform: scale(1);}
}
</style>