<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>التقرير الميداني اليومي - {{ $project->code }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0369a1;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
            margin-top: 5px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .meta-label {
            font-weight: bold;
            color: #64748b;
            width: 20%;
        }
        .meta-val {
            color: #0f172a;
            font-weight: bold;
            width: 30%;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0369a1;
            border-right: 3px solid #0284c7;
            padding-right: 8px;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        .content-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 15px;
            font-size: 12px;
            white-space: pre-wrap;
        }
        .blockers-box {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 15px;
        }
        .photos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .photo-cell {
            width: 50%;
            padding: 6px;
            vertical-align: top;
            text-align: center;
        }
        .photo-img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }
        .photo-caption {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="company-name" style="font-size: 16px; font-weight: bold; color: #0369a1;">{{ $tenant->name ?? 'شركة المقاولات' }}</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">سجل تجاري: {{ $tenant->cr_number ?? '-' }} | الرقم الضريبي: {{ $tenant->vat_number ?? '-' }}</div>
                </td>
                <td style="width: 40%; text-align: left; vertical-align: top;">
                    <div style="font-size: 15px; font-weight: bold; color: #0284c7;" dir="ltr">FieldOps KSA</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;"><span dir="ltr">{{ $report->report_date->format('Y-m-d') }}</span> :تاريخ التقرير</div>
                </td>
            </tr>
        </table>
        <div class="report-title">تقرير الموقع الميداني اليومي</div>
        <div style="font-size: 11px; text-align: center; color: #64748b; margin-top: 2px;" dir="ltr">Daily Site Report</div>
    </div>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td class="meta-label">المشروع:</td>
                <td class="meta-val">{{ $project->name }} <span dir="ltr" style="color: #64748b;">({{ $project->code }})</span></td>
                <td class="meta-label">المدينة:</td>
                <td class="meta-val">{{ $project->location_city }}</td>
            </tr>
            <tr>
                <td class="meta-label">المالك / العميل:</td>
                <td class="meta-val">{{ $project->client_name }}</td>
                <td class="meta-label">الاستشاري:</td>
                <td class="meta-val">{{ $project->consultant_name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td class="meta-label">مهندس الموقع:</td>
                <td class="meta-val">{{ $report->user->name ?? 'مهندس الموقع' }}</td>
                <td class="meta-label">حالة الطقس:</td>
                <td class="meta-val">{{ $report->weather_condition ?? 'معتدل' }}</td>
            </tr>
            <tr>
                <td class="meta-label">إجمالي العمالة:</td>
                <td class="meta-val" style="color: #0369a1;">{{ $report->manpower_count }} عامل/فني</td>
                <td class="meta-label">حالة التقرير:</td>
                <td class="meta-val">{{ $report->status === 'approved' ? 'معتمد' : 'مقدم' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">ملخص الأعمال المنفذة اليوم</div>
    <div class="content-box">
        {!! nl2br(e($report->work_summary)) !!}
    </div>

    @if($report->blockers_notes)
    <div class="section-title" style="color: #be123c; border-color: #f43f5e;">المعوقات والملاحظات الحرجة</div>
    <div class="blockers-box">
        {!! nl2br(e($report->blockers_notes)) !!}
    </div>
    @endif

    @if($media->count() > 0)
    <div class="section-title">توثيق الصور الميدانية (معرض الصور)</div>
    <table class="photos-table">
        @foreach($media->chunk(2) as $row)
        <tr>
            @foreach($row as $item)
            <td class="photo-cell">
                @php
                    $imgSrc = null;
                    $path = $item->thumbnail_path ?? $item->file_path;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        $imgSrc = $path;
                    } else {
                        $fullPath = storage_path('app/public/' . $path);
                        if (file_exists($fullPath)) {
                            $imgSrc = $fullPath;
                        }
                    }
                @endphp
                @if($imgSrc)
                    <img src="{{ $imgSrc }}" class="photo-img" alt="Site Photo"/>
                @else
                    <div style="padding: 20px; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 4px;">
                        صورة الموقع مأرشفة
                    </div>
                @endif
                <div class="photo-caption">
                    {{ $item->caption ?? 'صورة توثيقية' }}
                    @if($item->captured_at)
                    - {{ $item->captured_at->format('H:i') }}
                    @endif
                </div>
            </td>
            @endforeach
            @if($row->count() == 1)
            <td class="photo-cell"></td>
            @endif
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        تم استخراج هذا التقرير آلياً عبر منصة FieldOps KSA لإدارة العمليات الميدانية للمقاولات | <span dir="ltr">{{ date('Y-m-d H:i') }}</span>
    </div>
</body>
</html>
