@extends('layouts.app')

@section('page_title', 'المخزن')

<style>
    body {
        font-family: "Tahoma", sans-serif;
        background: linear-gradient(to bottom, #fff, #fce9d9);
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    /* العدادين */
    .stats-box {
        background: #fdf6f0;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        width: 220px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        font-size: 15px;
        margin: 10px;
        flex-shrink: 0;
    }

    .stats-box p:first-child {
        margin: 0;
        font-weight: bold;
        color: #444;
        font-size: 16px;
    }

    .stats-box p:last-child {
        margin: 10px 0 0;
        font-size: 22px;
        color: #333;
    }

    /* زرار الإضافة */
    #addButton {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: none;
        background: #ffcb9a;
        font-size: 48px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: 0.3s;
        margin: 0 40px;
        flex-shrink: 0;
    }

    #addButton:hover {
        background: #ffa94d;
        transform: scale(1.05);
    }

    /* الصف الأول (عدادات + زرار) */
    .header-row {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    /* مربع البحث */
    .search-box {
        margin: 20px auto;
        text-align: center;
    }

    .search-box input {
        padding: 14px 20px;
        width: 450px;
        max-width: 100%;
        border-radius: 25px;
        border: 1px solid #ddd;
        font-size: 15px;
        outline: none;
        transition: 0.2s;
        background: #fff;
    }

    .search-box input:focus {
        border-color: #ffcb9a;
        box-shadow: 0 0 6px rgba(255, 170, 80, 0.5);
    }

    /* الجدول */
    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    thead {
        background: rgba(255, 224, 178, 0.8);
    }

    thead th {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #444;
    }

    tbody tr {
        border-bottom: 1px solid #eee;
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(255, 247, 240, 0.7);
    }

    tbody td {
        padding: 14px 18px;
        font-size: 15px;
        color: #333;
    }

    /* الموبايل */
    @media (max-width: 768px) {
        .header-row {
            flex-direction: column;
        }

        #addButton {
            width: 80px;
            height: 80px;
            font-size: 36px;
            margin: 15px 0;
        }

        .search-box input {
            width: 100%;
        }

        table,

        thead,

        tbody,

        th,

        td,

        tr {
            display: block;
            width: 100%;
        }

        thead {
            display: none;
        }

        tbody tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        tbody td {
            text-align: right;
            padding: 8px 10px;
            position: relative;
            font-size: 14px;
        }

        tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: bold;
            color: #666;
        }
    }
</style>

@section('content')

    <div class="container">

        {{-- الإشعارات --}}

        @if (session('success'))
            <div style="background: #d4edda; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#155724;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="background: #f8d7da; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#721c24;">
                {{ session('error') }}
            </div>
        @endif

        {{-- العدادين وزرار الإضافة --}}

        <div class="header-row">

            <div class="stats-box">

                <p>عدد المنتجات</p>

                <p>{{ $countItems }}</p>

            </div>

            <button id="addButton" data-bs-toggle="modal" data-bs-target="#chooseActionModal">+</button>


            <div class="stats-box">

                <p>عدد الأصناف</p>

                <p>{{ $countProducts }}</p>

            </div>

        </div>

        {{-- البحث --}}

        <div class="search-box">

            <input type="text" id="searchBox" placeholder="🔍 بحث عن منتج">

        </div>

        {{-- الجدول --}}

        <table>

            <thead>

                <tr>

                    <th>المعرف</th>

                    <th>اسم المنتج</th>

                    <th>السعر</th>

                    <th>التكلفة</th>

                    <th>العدد</th>

                </tr>

            </thead>

            <tbody id="productTable">

                @foreach ($products as $product)
                    <tr>

                        <td data-label="المعرف">{{ $product->id }}</td>

                        <td data-label="اسم المنتج">{{ $product->name }}</td>

                        <td data-label="السعر">{{ $product->price }}</td>

                        <td data-label="التكلفة">{{ $product->cost }}</td>

                        <td data-label="العدد">{{ $product->quantity }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        // البحث AJAX
        document.getElementById('searchBox').addEventListener('keyup', function() {
            let query = this.value;

            fetch("{{ route('products.search') }}?query=" + query)
                .then(response => response.json())
                .then(data => {
                    let tbody = document.getElementById('productTable');
                    tbody.innerHTML = "";

                    if (data.length > 0) {
                        data.forEach(item => {
                            tbody.innerHTML += `
                        <tr>
                            <td data-label="المعرف">${item.id}</td>
                            <td data-label="اسم المنتج">${item.name}</td>
                            <td data-label="السعر">${item.price}</td>
                            <td data-label="التكلفة">${item.cost}</td>
                            <td data-label="العدد">${item.quantity}</td>
                        </tr>
                    `;
                        });
                    }
                });
        });

        // زرار الإضافة
        document.getElementById('addButton').addEventListener('click', function() {

        });
    </script>




    {{-- المودالات --}}
    @include('products.modals.choose-action')
    @include('products.modals.add-product')
    @include('products.modals.add-quantity')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addQtyModal = document.getElementById('addQuantityModal');
            if (!addQtyModal) return;

            // لما المودال يفتح
            addQtyModal.addEventListener('shown.bs.modal', function() {
                const searchInput = document.getElementById('searchProduct');
                const resultsList = document.getElementById('searchResults');
                const form = document.getElementById('addQuantityForm');
                const productIdInput = document.getElementById('product_id');

                if (!searchInput || !resultsList || !form || !productIdInput) return;

                // reset كل مرة يفتح فيها المودال
                searchInput.value = '';
                resultsList.innerHTML = '';
                form.style.display = 'none';
                productIdInput.value = '';
                searchInput.focus();

                // علشان ما نربطش نفس الحدث أكتر من مرة
                if (searchInput.dataset.bound === '1') return;
                searchInput.dataset.bound = '1';

                // السيرش
                searchInput.addEventListener('keyup', function() {
                    const q = this.value.trim();
                    if (q.length < 1) {
                        resultsList.innerHTML = '';
                        form.style.display = 'none';
                        return;
                    }

                    fetch("{{ route('products.search') }}?query=" + encodeURIComponent(q))
                        .then(res => res.json())
                        .then(items => {
                            resultsList.innerHTML = '';

                            if (!items.length) {
                                resultsList.innerHTML =
                                    '<li class="list-group-item text-center text-muted">لا توجد نتائج</li>';
                                form.style.display = 'none';
                                return;
                            }

                            items.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.style.cursor = 'pointer';
                                li.textContent = `${item.name} (المعرف: ${item.id})`;

                                li.addEventListener('click', function() {
                                    productIdInput.value = item.id;
                                    form.action =
                                        "{{ route('products.addQuantity', ':id') }}"
                                        .replace(':id', item.id);

                                    form.style.display = 'block';
                                    resultsList.innerHTML = '';
                                    searchInput.value = item.name;
                                });

                                resultsList.appendChild(li);
                            });
                        })
                        .catch(err => {
                            console.error('Search error:', err);
                        });
                });
            });
        });
    </script>

@endsection
