<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تحليلات عامة — معاينة</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

<div class="container mx-auto px-4 py-6">
  <div class="flex flex-col lg:flex-row gap-6">

    <!-- LEFT -->
    <aside class="w-full lg:w-1/3 space-y-4">
      <!-- Filters placeholder -->
      <div class="bg-white rounded-lg p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700">الفلاتر</h3>
        <p class="mt-2 text-xs text-gray-400">اختيار التاريخ + القاعة</p>
      </div>

      <!-- Metrics -->
      <div class="grid grid-cols-2 gap-4">
        <!-- metric card -->
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-xs text-gray-500">إجمالي الحجوزات</div>
              <div class="text-2xl font-bold text-gray-900">123</div>
            </div>
            <div class="text-sm text-green-600 font-semibold">+4.2%</div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-xs text-gray-500">الإيرادات المقدرة</div>
              <div class="text-2xl font-bold text-gray-900">45,200 ج.م</div>
            </div>
            <div class="text-sm text-green-600 font-semibold">+2.1%</div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-xs text-gray-500">المشتركين</div>
              <div class="text-2xl font-bold text-gray-900">78</div>
            </div>
            <div class="text-sm text-red-600 font-semibold">-0.4%</div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-xs text-gray-500">منتجات منخفضة</div>
              <div class="text-2xl font-bold text-gray-900">6</div>
            </div>
            <div class="text-sm text-gray-600 font-semibold">0%</div>
          </div>
        </div>
      </div>

      <!-- Trends placeholder -->
      <div class="bg-white rounded-lg p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700">نظرة سريعة</h3>
        <div class="mt-4 h-20 bg-gray-100 rounded"></div>
        <div class="mt-3 h-20 bg-gray-100 rounded"></div>
      </div>
    </aside>

    <!-- RIGHT -->
    <main class="w-full lg:w-2/3 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700">حجوزات حسب اليوم</h3>
          <div class="mt-4 h-40 bg-gray-100 rounded"></div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700">أعلى القاعات</h3>
          <div class="mt-4 h-40 bg-gray-100 rounded"></div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg p-4 shadow-sm">
        <h3 class="text-base font-semibold text-gray-800">آخر الحجوزات</h3>
        <table class="mt-3 min-w-full text-sm">
          <thead class="text-left text-gray-600">
            <tr>
              <th class="p-2">رقم الحجز</th>
              <th class="p-2">القاعة</th>
              <th class="p-2">العميل</th>
              <th class="p-2">تاريخ</th>
              <th class="p-2">الحالة</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t">
              <td class="p-2">1</td>
              <td class="p-2">قاعة A</td>
              <td class="p-2">محمد علي</td>
              <td class="p-2">2025-09-25 12:00</td>
              <td class="p-2">مؤكد</td>
            </tr>
            <tr class="border-t">
              <td class="p-2">2</td>
              <td class="p-2">قاعة B</td>
              <td class="p-2">سارة</td>
              <td class="p-2">2025-09-24 18:30</td>
              <td class="p-2">ملغي</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

</body>
</html>
