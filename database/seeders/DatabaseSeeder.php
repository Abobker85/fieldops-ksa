<?php

namespace Database\Seeders;

use App\Models\BoqItem;
use App\Models\DailyReport;
use App\Models\DailyReportMedia;
use App\Models\PaymentClaim;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\SiteRequest;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $pmRole = Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);
        $engineerRole = Role::firstOrCreate(['name' => 'site_engineer', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // 2. Tenant
        $tenant = Tenant::create([
            'name' => 'شركة رواسي التشييد للمقاولات العامة',
            'cr_number' => '1010784932',
            'vat_number' => '310294857600003',
            'logo_url' => '/logo.png',
            'subscription_status' => 'active',
        ]);

        // 3. Users
        $defaultPassword = Hash::make('password123');

        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'م. فهد بن عبدالعزيز (المدير العام)',
            'email' => 'owner@fieldops.sa',
            'phone' => '+966501112233',
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $owner->assignRole($ownerRole);

        $pm = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'م. عبدالرحمن الشمري (مدير المشاريع)',
            'email' => 'pm@fieldops.sa',
            'phone' => '+966502223344',
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $pm->assignRole($pmRole);

        $engineer = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'م. سلطان العتيبي (مهندس الموقع)',
            'email' => 'engineer@fieldops.sa',
            'phone' => '+966503334455',
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $engineer->assignRole($engineerRole);

        $consultant = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'م. حسام الدوسري (استشاري المالك)',
            'email' => 'consultant@fieldops.sa',
            'phone' => '+966504445566',
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $consultant->assignRole($viewerRole);

        // 4. Project 1: Al-Olaya Tower
        $project1 = Project::create([
            'tenant_id' => $tenant->id,
            'code' => 'PRJ-2026-01',
            'name' => 'مشروع برج العليا السكني والتجاري',
            'client_name' => 'شركة أروقة الاستثمار العقاري',
            'consultant_name' => 'دار المعمار للاستشارات الهندسية',
            'location_city' => 'الرياض',
            'location_coordinates' => '24.7136, 46.6753',
            'contract_value' => 14500000.00,
            'start_date' => Carbon::parse('2026-01-15'),
            'expected_end_date' => Carbon::parse('2027-06-30'),
            'status' => 'active',
        ]);

        // Project 2: Khumra Complex
        $project2 = Project::create([
            'tenant_id' => $tenant->id,
            'code' => 'PRJ-2026-02',
            'name' => 'مجمع مستودعات الخمرة اللوجستية',
            'client_name' => 'المجموعة السعودية للخدمات اللوجستية',
            'consultant_name' => 'مكتب التقنية للاستشارات',
            'location_city' => 'جدة',
            'location_coordinates' => '21.3200, 39.2400',
            'contract_value' => 8200000.00,
            'start_date' => Carbon::parse('2026-03-01'),
            'expected_end_date' => Carbon::parse('2026-12-31'),
            'status' => 'active',
        ]);

        // 5. BOQ Items for Project 1
        BoqItem::create([
            'project_id' => $project1->id,
            'item_code' => 'BOQ-01',
            'description' => 'أعمال الحفر والإحلال وسند جوانب الحفر',
            'unit' => 'م3',
            'total_quantity' => 12500.00,
            'unit_price' => 75.00,
            'total_price' => 937500.00,
            'weight_percentage' => 10.00,
            'current_progress_percentage' => 100.00,
        ]);

        BoqItem::create([
            'project_id' => $project1->id,
            'item_code' => 'BOQ-02',
            'description' => 'صب خرسانة القواعد المسلحة واللبشة الخرسانية عيار 350',
            'unit' => 'م3',
            'total_quantity' => 3200.00,
            'unit_price' => 450.00,
            'total_price' => 1440000.00,
            'weight_percentage' => 25.00,
            'current_progress_percentage' => 80.00,
        ]);

        BoqItem::create([
            'project_id' => $project1->id,
            'item_code' => 'BOQ-03',
            'description' => 'توريد وصب أعمدة وجدران القبو والخزان الأرضي',
            'unit' => 'م3',
            'total_quantity' => 1800.00,
            'unit_price' => 520.00,
            'total_price' => 936000.00,
            'weight_percentage' => 20.00,
            'current_progress_percentage' => 50.00,
        ]);

        BoqItem::create([
            'project_id' => $project1->id,
            'item_code' => 'BOQ-04',
            'description' => 'أعمال شدات وحدادة سقف الدور الأرضي وميزانين',
            'unit' => 'م2',
            'total_quantity' => 4500.00,
            'unit_price' => 280.00,
            'total_price' => 1260000.00,
            'weight_percentage' => 25.00,
            'current_progress_percentage' => 20.00,
        ]);

        BoqItem::create([
            'project_id' => $project1->id,
            'item_code' => 'BOQ-05',
            'description' => 'أعمال المباني والعزل المائي والحراري للأدوار السفلية',
            'unit' => 'م2',
            'total_quantity' => 6000.00,
            'unit_price' => 120.00,
            'total_price' => 720000.00,
            'weight_percentage' => 20.00,
            'current_progress_percentage' => 0.00,
        ]);

        // 6. Daily Site Reports
        $report1 = DailyReport::create([
            'project_id' => $project1->id,
            'user_id' => $engineer->id,
            'report_date' => Carbon::parse('2026-09-18'),
            'weather_condition' => 'مشمس / 34°م',
            'manpower_count' => 38,
            'work_summary' => "1. استكمال حدادة وتسليح سقف الدور الأرضي في المنطقة المحصورة بين المحاور A-4 و C-8.\n2. استلام أعمال النجارة لسقف الميزانين من قِبل مهندس الاستشاري وتسجيل ملاحظات بسيطة تم معالجتها فوراً.\n3. وصول دفعة حديد التسليح زامل سابك سعة 50 طن وتفريغها في الموقع.",
            'blockers_notes' => "تأخر وصول خلاطة الخرسانة المسائية لمدة 45 دقيقة بسبب الازدحام المروري عند مدخل طريق الملك فهد، وتم استئناف الصب بشكل طبيعي.",
            'status' => 'submitted',
        ]);

        $report2 = DailyReport::create([
            'project_id' => $project1->id,
            'user_id' => $engineer->id,
            'report_date' => Carbon::parse('2026-09-17'),
            'weather_condition' => 'صافي / 36°م',
            'manpower_count' => 34,
            'work_summary' => "1. معالجة خرسانة أعمدة القبو بالرش المائي الكثيف ولف الخيش.\n2. تركيب شبك التثبيت وبدء أعمال التمديدات الكهربائية والصحية المعلقة.",
            'blockers_notes' => null,
            'status' => 'approved',
        ]);

        // 7. Media for Report 1
        DailyReportMedia::create([
            'daily_report_id' => $report1->id,
            'file_path' => 'https://images.unsplash.com/photo-1541888946425-d0fbb1861564?w=800&q=80',
            'thumbnail_path' => 'https://images.unsplash.com/photo-1541888946425-d0fbb1861564?w=360&q=80',
            'file_type' => 'image',
            'caption' => 'أعمال حدادة تسليح سقف الدور الأرضي',
            'geo_latitude' => 24.71360000,
            'geo_longitude' => 46.67530000,
            'captured_at' => Carbon::parse('2026-09-18 10:30:00'),
        ]);

        DailyReportMedia::create([
            'daily_report_id' => $report1->id,
            'file_path' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=80',
            'thumbnail_path' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=360&q=80',
            'file_type' => 'image',
            'caption' => 'فحص استقامة الشدات الخشبية مع الاستشاري',
            'geo_latitude' => 24.71361000,
            'geo_longitude' => 46.67532000,
            'captured_at' => Carbon::parse('2026-09-18 11:45:00'),
        ]);

        // 8. Documents & Drawings
        ProjectDocument::create([
            'project_id' => $project1->id,
            'title' => 'مخطط تسليح سقف الدور الأرضي والميزانين',
            'document_code' => 'DWG-ST-201',
            'discipline' => 'structural',
            'current_revision' => 'Rev 02',
            'file_path' => 'documents/DWG-ST-201.pdf',
            'is_approved_for_construction' => true,
            'uploaded_by' => $pm->id,
        ]);

        ProjectDocument::create([
            'project_id' => $project1->id,
            'title' => 'المسقط المعماري للدور الأرضي والواجهات الشرقية',
            'document_code' => 'DWG-AR-101',
            'discipline' => 'architectural',
            'current_revision' => 'Rev 01',
            'file_path' => 'documents/DWG-AR-101.pdf',
            'is_approved_for_construction' => true,
            'uploaded_by' => $pm->id,
        ]);

        ProjectDocument::create([
            'project_id' => $project1->id,
            'title' => 'مخطط تمديدات مكافحة الحريق والصرف الصحي',
            'document_code' => 'DWG-ME-301',
            'discipline' => 'mechanical',
            'current_revision' => 'Rev 00',
            'file_path' => 'documents/DWG-ME-301.pdf',
            'is_approved_for_construction' => false,
            'uploaded_by' => $pm->id,
        ]);

        // 9. Site Requests
        SiteRequest::create([
            'project_id' => $project1->id,
            'requested_by' => $engineer->id,
            'type' => 'WIR',
            'request_number' => 'WIR-001',
            'title' => 'طلب استلام حدادة سقف الدور الأرضي Zone-A',
            'description' => 'يرجى التكرم بمعاينة واستلام حدادة التسليح والوصلات وتجهيزات كابلات الشد المسبق لمنطقة A.',
            'location_details' => 'سقف الدور الأرضي / المحور B-4 إلى E-8',
            'estimated_cost_impact' => 0.00,
            'status' => 'approved',
            'response_notes' => 'تم المعاينة والموافقة مع التنبيه على زيادة البسكوت الخرساني عند الزوايا.',
        ]);

        SiteRequest::create([
            'project_id' => $project1->id,
            'requested_by' => $engineer->id,
            'type' => 'RFI',
            'request_number' => 'RFI-001',
            'title' => 'استفسار حول تعارض مسار ماسورة المطر مع كمرة C-12',
            'description' => 'يوجد تداخل بين فتحة تصريف المطر والحديد الرئيسي لكمرة المحور C-12، مطلوب إفادة بتعديل المسار.',
            'location_details' => 'السقف العلوي / المحور C-12',
            'estimated_cost_impact' => 0.00,
            'status' => 'under_review',
            'response_notes' => 'جاري مراجعة المخطط الإنشائي مع المصمم.',
        ]);

        SiteRequest::create([
            'project_id' => $project1->id,
            'requested_by' => $pm->id,
            'type' => 'VARIATION_ORDER',
            'request_number' => 'VO-001',
            'title' => 'أمر تغيير: إضافة نظام عزل مائي إضافي للبدروم بناء على طلب المالك',
            'description' => 'توريد وتركيب طبقتين ممبرين SBS بسمك 4 ملم إضافية حول جدران البدروم المعرضة للرطوبة.',
            'location_details' => 'محيط البدروم بالكامل',
            'estimated_cost_impact' => 145000.00,
            'status' => 'pending',
            'response_notes' => null,
        ]);

        // 10. Payment Claims
        PaymentClaim::create([
            'project_id' => $project1->id,
            'claim_number' => 'CLM-01',
            'period_start' => Carbon::parse('2026-01-15'),
            'period_end' => Carbon::parse('2026-03-31'),
            'claimed_amount' => 937500.00,
            'approved_amount' => 937500.00,
            'vat_amount' => 140625.00,
            'status' => 'paid',
        ]);

        PaymentClaim::create([
            'project_id' => $project1->id,
            'claim_number' => 'CLM-02',
            'period_start' => Carbon::parse('2026-04-01'),
            'period_end' => Carbon::parse('2026-06-30'),
            'claimed_amount' => 1150000.00,
            'approved_amount' => 1150000.00,
            'vat_amount' => 172500.00,
            'status' => 'certified',
        ]);

        PaymentClaim::create([
            'project_id' => $project1->id,
            'claim_number' => 'CLM-03',
            'period_start' => Carbon::parse('2026-07-01'),
            'period_end' => Carbon::parse('2026-09-15'),
            'claimed_amount' => 840000.00,
            'approved_amount' => null,
            'vat_amount' => 126000.00,
            'status' => 'submitted',
        ]);
    }
}
