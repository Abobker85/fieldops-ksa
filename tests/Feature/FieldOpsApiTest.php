<?php

namespace Tests\Feature;

use App\Models\BoqItem;
use App\Models\DailyReport;
use App\Models\PaymentClaim;
use App\Models\Project;
use App\Models\SiteRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FieldOpsApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'site_engineer', 'guard_name' => 'web']);

        $this->tenantA = Tenant::create([
            'name' => 'شركة أ للمقاولات',
            'cr_number' => '1010111111',
            'vat_number' => '300000000100003',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'شركة ب للتشييد',
            'cr_number' => '1010222222',
            'vat_number' => '300000000200003',
        ]);

        $this->userA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مهندس أ',
            'email' => 'user_a@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'مهندس ب',
            'email' => 'user_b@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'user_a@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'tenant',
                    ],
                ],
            ]);
    }

    public function test_invalid_login_fails(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'user_a@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_multi_tenancy_data_isolation(): void
    {
        // Create project for Tenant A
        $projectA = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-A',
            'name' => 'مشروع الشركة أ',
            'client_name' => 'العميل أ',
            'location_city' => 'الرياض',
            'contract_value' => 1000000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // Create project for Tenant B
        $projectB = Project::create([
            'tenant_id' => $this->tenantB->id,
            'code' => 'PRJ-B',
            'name' => 'مشروع الشركة ب',
            'client_name' => 'العميل ب',
            'location_city' => 'جدة',
            'contract_value' => 2000000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // User A must ONLY see Project A
        $response = $this->actingAs($this->userA)->getJson('/api/v1/projects');
        $response->assertStatus(200);

        $projects = $response->json('data');
        $this->assertCount(1, $projects);
        $this->assertEquals('PRJ-A', $projects[0]['code']);
    }

    public function test_boq_weighted_progress_calculation(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-BOQ',
            'name' => 'مشروع حساب الكميات',
            'client_name' => 'عميل اختبار',
            'location_city' => 'الدمام',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // Item 1: Weight 40%, Progress 50% -> Contributes 20%
        $item1 = BoqItem::create([
            'project_id' => $project->id,
            'item_code' => 'BOQ-01',
            'description' => 'بند 1',
            'unit' => 'م2',
            'total_quantity' => 100,
            'unit_price' => 1000,
            'total_price' => 100000,
            'weight_percentage' => 40,
            'current_progress_percentage' => 50,
        ]);

        // Item 2: Weight 60%, Progress 25% -> Contributes 15%
        $item2 = BoqItem::create([
            'project_id' => $project->id,
            'item_code' => 'BOQ-02',
            'description' => 'بند 2',
            'unit' => 'م3',
            'total_quantity' => 200,
            'unit_price' => 2000,
            'total_price' => 400000,
            'weight_percentage' => 60,
            'current_progress_percentage' => 25,
        ]);

        // Total weighted progress should be: 20 + 15 = 35%
        $response = $this->actingAs($this->userA)->getJson("/api/v1/projects/{$project->id}/boq");
        $response->assertStatus(200);
        $this->assertEquals(35.0, (float) $response->json('data.weighted_progress'));

        // Now update Item 2 progress to 50% -> Contributes 30% -> Total becomes 50%
        $patchResponse = $this->actingAs($this->userA)->patchJson("/api/v1/boq/{$item2->id}/progress", [
            'current_progress_percentage' => 50,
        ]);

        $patchResponse->assertStatus(200)
            ->assertJsonPath('data.project_weighted_progress', 50);
    }

    public function test_daily_report_creation_and_pdf_export(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-REP',
            'name' => 'مشروع التقارير',
            'client_name' => 'عميل التقارير',
            'location_city' => 'الرياض',
            'contract_value' => 700000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $postResponse = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/daily-reports", [
            'report_date' => '2026-09-18',
            'weather_condition' => 'مشمس / 35°م',
            'manpower_count' => 25,
            'work_summary' => 'تم استكمال أعمال الصب والتشطيبات الأولية',
            'blockers_notes' => 'لا توجد معوقات',
            'status' => 'submitted',
        ]);

        $postResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $reportId = $postResponse->json('data.id');

        // Test PDF export endpoint
        $pdfResponse = $this->actingAs($this->userA)->get("/api/v1/daily-reports/{$reportId}/export-pdf");
        $pdfResponse->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_site_request_creation_and_status_update(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-REQ',
            'name' => 'مشروع الطلبات',
            'client_name' => 'عميل الطلبات',
            'location_city' => 'الرياض',
            'contract_value' => 300000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $createResponse = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/requests", [
            'type' => 'WIR',
            'title' => 'طلب استلام حديد تسليح القواعد',
            'description' => 'جاهز للاستلام والمعاينة الهندسية',
            'location_details' => 'المنطقة الشمالية',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.type', 'WIR')
            ->assertJsonPath('data.status', 'pending');

        $requestId = $createResponse->json('data.id');

        $patchResponse = $this->actingAs($this->userA)->patchJson("/api/v1/requests/{$requestId}/status", [
            'status' => 'approved',
            'response_notes' => 'معتمد بدون ملاحظات',
        ]);

        $patchResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_document_upload_and_listing(): void
    {
        Storage::fake('public');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-DOC',
            'name' => 'مشروع المخططات',
            'client_name' => 'عميل المخططات',
            'location_city' => 'مكة',
            'contract_value' => 450000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $file = UploadedFile::fake()->create('plan.pdf', 500, 'application/pdf');

        $uploadResponse = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'المخطط الإنشائي العام',
            'document_code' => 'DWG-001',
            'discipline' => 'structural',
            'current_revision' => 'Rev 01',
            'is_approved_for_construction' => true,
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(201)
            ->assertJsonPath('data.is_approved_for_construction', true);

        $listResponse = $this->actingAs($this->userA)->getJson("/api/v1/projects/{$project->id}/documents?ifc_only=1");
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));
    }

    public function test_cross_tenant_isolation_on_child_resources(): void
    {
        Storage::fake('public');

        $projectA = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-A-SEC',
            'name' => 'مشروع أ الأمني',
            'client_name' => 'العميل أ',
            'location_city' => 'الرياض',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $reportA = DailyReport::create([
            'project_id' => $projectA->id,
            'user_id' => $this->userA->id,
            'report_date' => '2026-09-18',
            'weather_condition' => 'معتدل',
            'manpower_count' => 10,
            'work_summary' => 'أعمال خاصة بالشركة أ',
            'status' => 'submitted',
        ]);

        $itemA = BoqItem::create([
            'project_id' => $projectA->id,
            'item_code' => 'BOQ-SEC',
            'description' => 'بند سري',
            'unit' => 'م2',
            'total_quantity' => 100,
            'unit_price' => 500,
            'total_price' => 50000,
            'weight_percentage' => 50,
            'current_progress_percentage' => 10,
        ]);

        $reqA = SiteRequest::create([
            'project_id' => $projectA->id,
            'requested_by' => $this->userA->id,
            'type' => 'WIR',
            'request_number' => 'WIR-SEC-01',
            'title' => 'طلب خاص بشركة أ',
            'description' => 'تفاصيل خاصة',
            'status' => 'pending',
        ]);

        // 1. User B must NOT be able to view Report A
        $response = $this->actingAs($this->userB)->getJson("/api/v1/daily-reports/{$reportA->id}");
        $this->assertContains($response->status(), [403, 404]);

        // 2. User B must NOT be able to export PDF of Report A
        $pdfResponse = $this->actingAs($this->userB)->get("/api/v1/daily-reports/{$reportA->id}/export-pdf");
        $this->assertContains($pdfResponse->status(), [403, 404]);

        // 3. User B must NOT be able to upload media to Report A
        $fakeImg = UploadedFile::fake()->image('hack.jpg');
        $uploadResp = $this->actingAs($this->userB)->postJson("/api/v1/daily-reports/{$reportA->id}/media", [
            'file' => $fakeImg,
        ]);
        $this->assertContains($uploadResp->status(), [403, 404]);

        // 4. User B must NOT be able to update BOQ progress of Item A
        $patchBoqResp = $this->actingAs($this->userB)->patchJson("/api/v1/boq/{$itemA->id}/progress", [
            'current_progress_percentage' => 90,
        ]);
        $this->assertContains($patchBoqResp->status(), [403, 404]);
        $this->assertEquals(10.0, (float) $itemA->fresh()->current_progress_percentage);

        // 5. User B must NOT be able to approve Site Request A
        $patchReqResp = $this->actingAs($this->userB)->patchJson("/api/v1/requests/{$reqA->id}/status", [
            'status' => 'approved',
        ]);
        $this->assertContains($patchReqResp->status(), [403, 404]);
        $this->assertEquals('pending', $reqA->fresh()->status);
    }

    public function test_role_authorization_on_sensitive_actions(): void
    {
        Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $engineer = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مهندس ميداني',
            'email' => 'eng_role@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $engineer->assignRole('site_engineer');

        $pm = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مدير مشاريع',
            'email' => 'pm_role@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $pm->assignRole('pm');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-ROLE',
            'name' => 'مشروع اختبار الصلاحيات',
            'client_name' => 'عميل الصلاحيات',
            'location_city' => 'الرياض',
            'contract_value' => 800000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $request = SiteRequest::create([
            'tenant_id' => $this->tenantA->id,
            'project_id' => $project->id,
            'requested_by' => $engineer->id,
            'type' => 'WIR',
            'request_number' => 'WIR-ROLE-01',
            'title' => 'طلب بحاجة لاعتماد',
            'description' => 'وصف الطلب',
            'status' => 'pending',
        ]);

        // 1. Site Engineer must NOT be allowed to approve requests (403)
        $respEng = $this->actingAs($engineer)->patchJson("/api/v1/requests/{$request->id}/status", [
            'status' => 'approved',
        ]);
        $respEng->assertStatus(403);
        $this->assertEquals('pending', $request->fresh()->status);

        // 2. PM CAN approve requests (200)
        $respPm = $this->actingAs($pm)->patchJson("/api/v1/requests/{$request->id}/status", [
            'status' => 'approved',
            'response_notes' => 'معتمد من مدير المشروع',
        ]);
        $respPm->assertStatus(200);
        $this->assertEquals('approved', $request->fresh()->status);

        // 3. Site Engineer must NOT create new projects (403)
        $createProjResp = $this->actingAs($engineer)->postJson('/api/v1/projects', [
            'code' => 'PRJ-UNAUTH',
            'name' => 'مشروع غير مصرح',
            'client_name' => 'عميل',
            'location_city' => 'الرياض',
            'contract_value' => 100000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);
        $createProjResp->assertStatus(403);

        // 4. Site Engineer must NOT create payment claims (403)
        $claimResp = $this->actingAs($engineer)->postJson("/api/v1/projects/{$project->id}/claims", [
            'claim_number' => 'CLM-UNAUTH',
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'claimed_amount' => 50000,
        ]);
        $claimResp->assertStatus(403);

        // 5. PM CAN create payment claims (201)
        $pmClaimResp = $this->actingAs($pm)->postJson("/api/v1/projects/{$project->id}/claims", [
            'claim_number' => 'CLM-AUTH',
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'claimed_amount' => 50000,
        ]);
        $pmClaimResp->assertStatus(201);
    }

    public function test_daily_report_concurrency_and_status_update(): void
    {
        Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);

        $pm = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مدير معتمد',
            'email' => 'pm_rep@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $pm->assignRole('pm');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-CONC',
            'name' => 'مشروع التزامن',
            'client_name' => 'عميل التزامن',
            'location_city' => 'الرياض',
            'contract_value' => 600000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // Submission 1
        $resp1 = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/daily-reports", [
            'report_date' => '2026-09-18',
            'manpower_count' => 15,
            'work_summary' => 'التقرير الأول في الصباح',
            'status' => 'submitted',
        ]);
        $resp1->assertStatus(201);
        $reportId = $resp1->json('data.id');

        // Submission 2 on the SAME date (should update gracefully without 500 duplicate key error)
        $resp2 = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/daily-reports", [
            'report_date' => '2026-09-18',
            'manpower_count' => 22,
            'work_summary' => 'تحديث مسائي للأعمال',
            'status' => 'submitted',
        ]);
        $resp2->assertStatus(200);
        $this->assertEquals(22, $resp2->json('data.manpower_count'));
        $this->assertEquals($reportId, $resp2->json('data.id'));

        // PM approves the report
        $patchStatus = $this->actingAs($pm)->patchJson("/api/v1/daily-reports/{$reportId}/status", [
            'status' => 'approved',
        ]);
        $patchStatus->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_boq_weight_percentage_validation(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->userA->assignRole('owner');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-WEIGHT',
            'name' => 'مشروع فحص الأوزان',
            'client_name' => 'عميل',
            'location_city' => 'الرياض',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // Item 1: 70%
        $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/boq", [
            'item_code' => 'BOQ-W1',
            'description' => 'بند 1',
            'unit' => 'م2',
            'total_quantity' => 10,
            'unit_price' => 100,
            'weight_percentage' => 70,
        ])->assertStatus(201);

        // Item 2: 40% (70 + 40 = 110 > 100, must be rejected with 422)
        $respOver = $this->actingAs($this->userA)->postJson("/api/v1/projects/{$project->id}/boq", [
            'item_code' => 'BOQ-W2',
            'description' => 'بند 2',
            'unit' => 'م2',
            'total_quantity' => 10,
            'unit_price' => 100,
            'weight_percentage' => 40,
        ]);
        $respOver->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_payment_claim_status_lifecycle(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->userA->assignRole('owner');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-CLM',
            'name' => 'مشروع المستخلصات',
            'client_name' => 'عميل المستخلص',
            'location_city' => 'الرياض',
            'contract_value' => 1000000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $claim = PaymentClaim::create([
            'tenant_id' => $this->tenantA->id,
            'project_id' => $project->id,
            'claim_number' => 'CLM-TEST-01',
            'period_start' => '2026-01-01',
            'period_end' => '2026-03-31',
            'claimed_amount' => 200000,
            'vat_amount' => 30000,
            'status' => 'submitted',
        ]);

        // Certify claim
        $certResp = $this->actingAs($this->userA)->patchJson("/api/v1/claims/{$claim->id}/status", [
            'status' => 'certified',
            'approved_amount' => 190000,
        ]);
        $certResp->assertStatus(200)
            ->assertJsonPath('data.status', 'certified')
            ->assertJsonPath('data.approved_amount', '190000.00');

        // Mark paid
        $payResp = $this->actingAs($this->userA)->patchJson("/api/v1/claims/{$claim->id}/status", [
            'status' => 'paid',
        ]);
        $payResp->assertStatus(200)
            ->assertJsonPath('data.status', 'paid');
    }

    public function test_whatsapp_and_share_token_pdf_download(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-SHARE',
            'name' => 'مشروع المشاركة',
            'client_name' => 'عميل المشاركة',
            'location_city' => 'الرياض',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $report = DailyReport::create([
            'project_id' => $project->id,
            'user_id' => $this->userA->id,
            'report_date' => '2026-09-18',
            'weather_condition' => 'معتدل',
            'manpower_count' => 12,
            'work_summary' => 'تقرير للمشاركة عبر الواتساب',
            'status' => 'approved',
        ]);

        $this->assertNotEmpty($report->share_token);
        $this->assertStringContainsString('share_token=', $report->export_url);

        // 1. Unauthenticated request with NO token fails with 401
        $unauthResp = $this->get("/api/v1/daily-reports/{$report->id}/export-pdf");
        $unauthResp->assertStatus(401);

        // 2. Unauthenticated request WITH valid share_token (from WhatsApp) succeeds with 200 PDF
        $validShareResp = $this->get("/api/v1/daily-reports/{$report->id}/export-pdf?share_token={$report->share_token}");
        $validShareResp->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        // 3. Request with tampered share_token fails with 403
        $tamperedResp = $this->get("/api/v1/daily-reports/{$report->id}/export-pdf?share_token=invalid-fake-token");
        $tamperedResp->assertStatus(403);
    }

    public function test_document_revision_and_ifc_superseding(): void
    {
        Storage::fake('public');
        Role::firstOrCreate(['name' => 'pm', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'site_engineer', 'guard_name' => 'web']);

        $pm = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مدير المشروع',
            'email' => 'pm_doc@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $pm->assignRole('pm');

        $engineer = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'مهندس المشروع',
            'email' => 'eng_doc@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $engineer->assignRole('site_engineer');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-IFC',
            'name' => 'مشروع المخططات IFC',
            'client_name' => 'عميل',
            'location_city' => 'الرياض',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        $file1 = UploadedFile::fake()->create('rev01.pdf', 300, 'application/pdf');
        $file2 = UploadedFile::fake()->create('rev02.pdf', 300, 'application/pdf');
        $file3 = UploadedFile::fake()->create('rev03.pdf', 300, 'application/pdf');

        // 1. PM uploads Rev 01 as IFC
        $resp1 = $this->actingAs($pm)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'مخطط الأساسات',
            'document_code' => 'DWG-FD-01',
            'discipline' => 'structural',
            'current_revision' => 'Rev 01',
            'is_approved_for_construction' => true,
            'file' => $file1,
        ]);
        $resp1->assertStatus(201);
        $doc1Id = $resp1->json('data.id');
        $this->assertTrue($resp1->json('data.is_approved_for_construction'));

        // 2. PM uploads Rev 02 as IFC -> Rev 01 must be superseded (is_approved_for_construction = false)
        $resp2 = $this->actingAs($pm)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'مخطط الأساسات المحدث',
            'document_code' => 'DWG-FD-01',
            'discipline' => 'structural',
            'current_revision' => 'Rev 02',
            'is_approved_for_construction' => true,
            'file' => $file2,
        ]);
        $resp2->assertStatus(201);
        $this->assertTrue($resp2->json('data.is_approved_for_construction'));

        // Confirm Rev 01 is now false
        $this->assertFalse((bool) \App\Models\ProjectDocument::find($doc1Id)->is_approved_for_construction);

        // 3. Site Engineer trying to upload as IFC should be rejected with 403
        $respEngIfc = $this->actingAs($engineer)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'مخطط غير معتمد',
            'document_code' => 'DWG-FD-01',
            'discipline' => 'structural',
            'is_approved_for_construction' => true,
            'file' => $file3,
        ]);
        $respEngIfc->assertStatus(403);

        // 4. Site Engineer uploads without revision -> should auto-increment to Rev 03
        $respEngAuto = $this->actingAs($engineer)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'مخطط دراسة',
            'document_code' => 'DWG-FD-01',
            'discipline' => 'structural',
            'is_approved_for_construction' => false,
            'file' => $file3,
        ]);
        $respEngAuto->assertStatus(201)
            ->assertJsonPath('data.current_revision', 'Rev 03');
    }

    public function test_viewer_role_cannot_post_sensitive_site_records(): void
    {
        Storage::fake('public');
        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $viewer = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'استشاري المالك المشاهد',
            'email' => 'viewer_guard@test.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $viewer->assignRole('viewer');

        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-VIEWER-RESTRICT',
            'name' => 'مشروع المقيد',
            'client_name' => 'عميل',
            'location_city' => 'الرياض',
            'contract_value' => 500000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        // 1. Viewer CANNOT create daily reports
        $respReport = $this->actingAs($viewer)->postJson("/api/v1/projects/{$project->id}/daily-reports", [
            'report_date' => '2026-09-18',
            'manpower_count' => 10,
            'work_summary' => 'غير مسموح للمشاهد',
        ]);
        $respReport->assertStatus(403);

        // 2. Viewer CANNOT upload documents
        $fakeFile = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $respDoc = $this->actingAs($viewer)->postJson("/api/v1/projects/{$project->id}/documents", [
            'title' => 'مخطط',
            'document_code' => 'DWG-V',
            'discipline' => 'architectural',
            'file' => $fakeFile,
        ]);
        $respDoc->assertStatus(403);

        // 3. Viewer CANNOT submit site requests
        $respReq = $this->actingAs($viewer)->postJson("/api/v1/projects/{$project->id}/requests", [
            'type' => 'WIR',
            'title' => 'طلب غير مصرح',
            'description' => 'وصف',
        ]);
        $respReq->assertStatus(403);
    }

    public function test_executive_summary_calculation(): void
    {
        $project = Project::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'PRJ-EXEC',
            'name' => 'مشروع الملخص التنفيذي',
            'client_name' => 'عميل تنفيذي',
            'location_city' => 'الرياض',
            'contract_value' => 1000000,
            'start_date' => '2026-01-01',
            'expected_end_date' => '2026-12-31',
        ]);

        BoqItem::create([
            'project_id' => $project->id,
            'item_code' => 'B-01',
            'description' => 'بند',
            'unit' => 'م2',
            'total_quantity' => 10,
            'unit_price' => 100,
            'total_price' => 1000,
            'weight_percentage' => 100,
            'current_progress_percentage' => 75,
        ]);

        DailyReport::create([
            'project_id' => $project->id,
            'user_id' => $this->userA->id,
            'report_date' => '2026-09-18',
            'manpower_count' => 45,
            'work_summary' => 'أعمال يومية مكثفة',
            'status' => 'submitted',
        ]);

        PaymentClaim::create([
            'tenant_id' => $this->tenantA->id,
            'project_id' => $project->id,
            'claim_number' => 'CLM-EX-1',
            'period_start' => '2026-01-01',
            'period_end' => '2026-03-31',
            'claimed_amount' => 250000,
            'approved_amount' => 240000,
            'vat_amount' => 37500,
            'status' => 'certified',
        ]);

        $response = $this->actingAs($this->userA)->getJson("/api/v1/projects/{$project->id}/executive-summary");
        $response->assertStatus(200)
            ->assertJsonPath('data.weighted_progress', 75)
            ->assertJsonPath('data.today_or_latest_manpower', 45)
            ->assertJsonPath('data.total_claimed', 250000)
            ->assertJsonPath('data.total_approved_claims', 240000);
    }
}
