<table class="table table-bordered">
    <thead>
        <tr>
            <th>اسم الحجز</th>
            <th>القاعة</th>
            <th>الحضور</th>
            <th>من</th>
            <th>إلى</th>
            <th>إجمالي متوقع</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($bookings as $booking)
            <tr>
                <td>{{ $booking->title }}</td>
                <td>{{ $booking->hall->name }}</td>
                <td>{{ $booking->attendees }}</td>
                <td>{{ $booking->start_at->format('Y-m-d H:i') }}</td>
                <td>{{ $booking->end_at->format('Y-m-d H:i') }}</td>
                <td>{{ number_format($booking->estimated_total, 2) }} ج.م</td>
                <td>
                    @if ($booking->status === 'scheduled')
                        <form action="{{ route('bookings.start', $booking) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">ابدأ</button>
                        </form>
                    @endif

                    <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-sm btn-primary">تعديل</a>
                    <form action="{{ route('bookings.destroy', $booking) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">لا يوجد حجوزات</td></tr>
        @endforelse
    </tbody>
</table>
