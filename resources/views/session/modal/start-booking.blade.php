<div class="modal fade animate__animated animate__zoomIn" id="startBookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header text-dark rounded-top-4 d-flex align-items-center" style="background-color: #f0f2ff;">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto">⚡ ابدأ جلسة خاصة</h5>
            </div>

            <div class="modal-body px-4 pb-3 pt-4">
                <form id="startBookingForm" action="{{ route('bookings.start-now') }}" method="POST">
                    @csrf

                    {{-- القاعات --}}
                    <div class="mb-3">
                        <label class="form-label">اختار القاعة</label>
                        <div class="fancy-select-wrapper">
                            <select id="hallSelect" name="hall_id" class="fancy-select" required>
                                <option value="">-- اختر القاعة --</option>
                                @foreach($halls as $hall)
                                    <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- العدد --}}
                    <div class="mb-3">
                        <label class="form-label">عدد الأشخاص</label>
                        <div id="personsDisplayInModal" class="input-box" style="padding:10px;">1</div>
                    </div>

                    {{-- عرض التقدير / الحالة --}}
                    <div id="estimateBanner" class="p-3 rounded-3" style="display:none; background:#f8f9fb; border:1px solid #e6e9f4;">
                        <div id="estimateMessage" style="font-weight:600; margin-bottom:8px;">جارِ الحساب...</div>
                        <div id="estimateAmount" style="font-size:18px;"></div>
                        <div id="estimatePerHour" style="font-size:13px; color:#666;"></div>
                    </div>

                    {{-- رسالة تحذير --}}
                    <div id="ongoingWarning" class="alert alert-warning" style="display:none; margin-top:12px;">
                        <strong>تنبيه:</strong> <span id="ongoingText"></span>
                    </div>

                    {{-- حقول مخفية --}}
                    <input type="hidden" id="modal_phone" name="client_phone" value="">
                    <input type="hidden" id="modal_name" name="client_name" value="">
                    <input type="hidden" id="modal_persons" name="attendees" value="1">

                    {{-- المدة ثابتة ساعة (60 دقيقة) --}}
                    <input type="hidden" name="duration_minutes" value="60">

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" id="startNowBtn" class="btn btn-primary w-50" disabled>بدء الآن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
