<?php

namespace Tests\Feature;

use App\Models\BoqItem;
use App\Models\DailyReport;
use App\Models\Project;
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
}
