<div class="bg-white rounded-lg p-4 shadow-sm">
  <div class="flex items-center justify-between">
    <div>
      <div class="text-xs text-gray-500">{{ $title }}</div>
      <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
    </div>
    <div class="text-sm text-green-600 font-semibold">{{ $delta ?? '' }}</div>
  </div>
  @if($slot->isNotEmpty())
    <div class="mt-2 text-xs text-gray-400">{{ $slot }}</div>
  @endif
</div>
