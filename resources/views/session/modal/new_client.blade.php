<div id="clientDataModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index:99999;">
    <div style="background:#fff; border-radius:12px; padding:20px; width:360px; max-width:95%; box-shadow:0 4px 12px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; color:#d9b2ad; text-align:center;">إكمال بيانات العميل</h3>
        <form id="clientDataForm">
            <input type="hidden" name="id" value="">

            <div style="margin-bottom:12px;">
                <label>العمر</label>
                <input type="number" name="age" value="" min="1" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
            </div>

            <div style="margin-bottom:12px;">
                <label>التخصص</label>
                <select name="specialization_id" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                    <option value="">اختر التخصص</option>
                    @foreach(\App\Models\Specialization::all() as $spec)
                        <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label>المرحلة الدراسية</label>
                <select name="education_stage_id" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                    <option value="">اختر المرحلة</option>
                    @foreach(\App\Models\EducationStage::all() as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="text-align:center; margin-top:10px;">
                <button type="submit" style="background:#d9b2ad; color:#fff; border:none; padding:8px 14px; border-radius:8px; cursor:pointer;">حفظ البيانات</button>
                <button type="button" id="closeClientModal" style="margin-left:6px; background:#ccc; color:#333; border:none; padding:8px 14px; border-radius:8px; cursor:pointer;">إغلاق</button>
            </div>
        </form>
    </div>
</div>
