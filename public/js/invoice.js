let cart = [];

// البحث عن المنتج
document.getElementById('product_search').addEventListener('input', function () {
    let query = this.value;

    if (query.length < 2) {
        document.getElementById('suggestions').innerHTML = '';
        return;
    }

    fetch(`/products/search?q=${query}`)
        .then(res => res.json())
        .then(data => {
            let suggestions = document.getElementById('suggestions');
            suggestions.innerHTML = '';

            data.forEach(p => {
                let div = document.createElement('div');
                div.classList.add('suggestion-item');
                div.innerText = `${p.id} - ${p.name} (${p.price} ج.م)`;
                div.onclick = function () {
                    selectProduct(p);
                };
                suggestions.appendChild(div);
            });
        });
});

// اختيار المنتج من القائمة
function selectProduct(product) {
    document.getElementById('product_id').value = product.id;
    document.getElementById('product_name').value = product.name;
    document.getElementById('product_price').value = product.price;

    // نظف الاقتراحات
    document.getElementById('suggestions').innerHTML = '';
    document.getElementById('product_search').value = '';
}

// إضافة المنتج للقائمة
function addProduct() {
    let id = document.getElementById('product_id').value;
    let name = document.getElementById('product_name').value;
    let price = parseFloat(document.getElementById('product_price').value);
    let qty = parseInt(document.getElementById('quantity').value);

    if (!id || !qty || qty <= 0) {
        alert('اختر منتج وأدخل الكمية');
        return;
    }

    let total = price * qty;

    cart.push({id, name, price, qty, total});
    renderCart();

    // نظف الحقول
    document.getElementById('product_id').value = '';
    document.getElementById('product_name').value = '';
    document.getElementById('product_price').value = '';
    document.getElementById('quantity').value = '';
}

// عرض القائمة في الجدول
function renderCart() {
    let table = document.getElementById('cart_table');
    table.innerHTML = '';
    let sum = 0;

    cart.forEach((item, index) => {
        sum += item.total;
        table.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>${item.qty}</td>
                <td>${item.price}</td>
                <td>${item.total}</td>
                <td><button onclick="removeItem(${index})">حذف</button></td>
            </tr>
        `;
    });

    document.getElementById('invoice_total').innerText = sum;
}

// حذف عنصر من القائمة
function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

// تجهيز البيانات قبل الحفظ
function prepareData() {
    document.getElementById('items_input').value = JSON.stringify(cart);
}
