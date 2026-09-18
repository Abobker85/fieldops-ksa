# وثيقة متطلبات منتج الـ MVP: منصة إدارة العمليات الميدانية للمقاولات (FieldOps KSA)

## 1. نظرة عامة ورؤية المنتج (Product Overview & Vision)

* **المشكلة:** اعتماد شركات المقاولات والتشطيب في السعودية على مجموعات الواتساب وملفات إكسل المتفرقة، مما يؤدي إلى ضياع سجل القرارات الميدانية، استخدام مخططات غير محدثة، تأخر اعتمادات الاستشاريين، وضياع حقوق أوامر التغيير والمستخلصات، فضلًا عن تعقيد وتكلفة أنظمة الـ ERP التقليدية.

* **الحل:** منصة ويب خفيفة (Mobile-First PWA) متخصصة في العمليات الميدانية، تربط مهندس الموقع بالإدارة والمالك لتوثيق اليوميات، إدارة المخططات، تتبع نسب الإنجاز والمستخلصات، وتوليد تقارير رسمية بضغطة زر.

* **الجمهور المستهدف:** شركات المقاولات المتوسطة والصغيرة، مؤسسات التشطيب والديكور، مدراء المشاريع، ومهندسو المواقع في المملكة العربية السعودية.

## 2. المكدس التقني والمعايير (Tech Stack & Architecture Rules)

### Backend:

* **Framework:** Laravel 11.x (PHP 8.3+)

* **Authentication:** Laravel Sanctum (Token-based API Authentication)

* **Authorization:** `spatie/laravel-permission` (Role-Based Access Control)

* **Database:** MySQL 8.0+ أو PostgreSQL 16+

* **Storage:** S3-Compatible Object Storage (Cloudflare R2 أو AWS بحزم الرياض)

* **Background Jobs & Queues:** Redis + Laravel Queue (لمعالجة الصور، ضغطها، وتوليد الـ PDF)

* **PDF Engine:** `barryvdh/laravel-dompdf` أو `spatie/browsershot`

### Frontend:

* **Framework:** Vue.js 3 (Composition API مع `<script setup>`)

* **Build Tool:** Vite

* **State Management:** Pinia

* **Routing:** Vue Router 4

* **Styling & UI:** Tailwind CSS (تفعيل `dir="rtl"` كإعداد افتراضي) + Lucide Icons

* **PWA:** `vite-plugin-pwa` لتسهيل تثبيت الموقع كتطبيق على هواتف المهندسين.

### Architectural Constraints:

1. **Multi-Tenancy:** نموذج قاعدة بيانات موحدة (Single DB Multi-tenancy) باستخدام `tenant_id` مربوط بكل الجداول ومحمي عبر Laravel Global Scopes.

2. **API Structure:** مسارات قياسية موحدة `api/v1/...` مع معالجة استجابات الـ JSON بنمط موحد:

   ```
   {
     "success": true,
     "data": {},
     "message": "Operation successful"
   }
   
   ```

3. **Data Integrity:** جميع المبالغ المالية تخزن بصيغة `decimal(15, 2)` بالريال السعودي (SAR)، والتواريخ تدعم الميلادي كمرجع مع إمكانية العرض الهجري.

## 3. الصلاحيات والمستخدمين (Roles & Permissions)

| **الدور (Role)** | **الصلاحيات الرئيسية** | 
| **Owner / General Manager** | الوصول الكامل لجميع المشاريع، التقارير المالية، الميزانيات، وحسابات الاشتراك. | 
| **Project Manager (PM)** | إدارة المشاريع المسندة له، اعتماد التقارير اليومية، رفع المستخلصات، وتعديل الـ BOQ. | 
| **Site Engineer** | إدخال التقرير اليومي، رفع الصور، تقديم طلبات الاستلام (WIR) وطلبات الاستفسار (RFI). | 
| **Client / Consultant (Viewer)** | الاطلاع على نسب الإنجاز، معرض الصور المعتمدة، وسجل الطلبات للمشروع الخاص به فقط. | 

## 4. مخطط قاعدة البيانات الشامل (Database Schema & Migrations)

### 1. جدول الشركات (Tenants)

```
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    cr_number VARCHAR(50) NULL, -- السجل التجاري
    vat_number VARCHAR(50) NULL, -- الرقم الضريبي
    logo_url VARCHAR(255) NULL,
    subscription_status ENUM('trial', 'active', 'suspended') DEFAULT 'trial',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

```

### 2. جدول المستخدمين (Users)

```
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

```

### 3. جدول المشاريع (Projects)

```
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL, -- كود المشروع مثل PRJ-2026-01
    name VARCHAR(191) NOT NULL,
    client_name VARCHAR(191) NOT NULL,
    consultant_name VARCHAR(191) NULL,
    location_city VARCHAR(100) NOT NULL,
    location_coordinates VARCHAR(100) NULL, -- Lat, Long
    contract_value DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    start_date DATE NOT NULL,
    expected_end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'on_hold') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

```

### 4. جدول سجل الموقع اليومي (Daily Site Reports - DSR)

```
CREATE TABLE daily_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL, -- المهندس المسجل
    report_date DATE NOT NULL,
    weather_condition VARCHAR(50) NULL,
    manpower_count INT UNSIGNED NOT NULL DEFAULT 0,
    work_summary TEXT NOT NULL,
    blockers_notes TEXT NULL, -- المعوقات والمشاكل
    status ENUM('draft', 'submitted', 'approved') DEFAULT 'submitted',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY project_date_unique (project_id, report_date),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

```

### 5. وسائط التقرير اليومي (Daily Report Media)

```
CREATE TABLE daily_report_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    daily_report_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NULL,
    file_type ENUM('image', 'video') DEFAULT 'image',
    caption VARCHAR(191) NULL,
    geo_latitude DECIMAL(10, 8) NULL,
    geo_longitude DECIMAL(11, 8) NULL,
    captured_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (daily_report_id) REFERENCES daily_reports(id) ON DELETE CASCADE
);

```

### 6. جدول المخططات والمستندات (Drawings & Documents)

```
CREATE TABLE project_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(191) NOT NULL,
    document_code VARCHAR(100) NOT NULL, -- مثل: DWG-AR-101
    discipline ENUM('architectural', 'structural', 'mechanical', 'electrical', 'contracts', 'permits') NOT NULL,
    current_revision VARCHAR(10) DEFAULT 'Rev 00',
    file_path VARCHAR(255) NOT NULL,
    is_approved_for_construction BOOLEAN DEFAULT FALSE, -- IFC
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
);

```

### 7. طلبات الموقع والاعتمادات (Site Requests: RFI & WIR)

```
CREATE TABLE site_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    type ENUM('RFI', 'WIR', 'VARIATION_ORDER') NOT NULL,
    request_number VARCHAR(50) NOT NULL, -- WIR-001 / RFI-001
    title VARCHAR(191) NOT NULL,
    description TEXT NOT NULL,
    location_details VARCHAR(191) NULL, -- الطابق الأرضي / المحور B-4
    estimated_cost_impact DECIMAL(15, 2) DEFAULT 0.00, -- خاص بأوامر التغيير
    status ENUM('pending', 'approved', 'rejected', 'under_review') DEFAULT 'pending',
    response_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT
);

```

### 8. جدول بنود الأعمال والمستخلصات (BOQ Items & Claims)

```
CREATE TABLE boq_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    item_code VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    unit VARCHAR(20) NOT NULL, -- م2، م3، عدد، مقطوعية
    total_quantity DECIMAL(12, 2) NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    total_price DECIMAL(15, 2) NOT NULL,
    weight_percentage DECIMAL(5, 2) NOT NULL, -- الوزن النسبي للمشروع
    current_progress_percentage DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE payment_claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    claim_number VARCHAR(50) NOT NULL, -- المستخلص رقم 1
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    claimed_amount DECIMAL(15, 2) NOT NULL,
    approved_amount DECIMAL(15, 2) NULL,
    vat_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00, -- 15% ضريبة القيمة المضافة
    status ENUM('submitted', 'certified', 'paid_partially', 'paid') DEFAULT 'submitted',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

```

## 5. مواصفات الـ API والمخرجات البرمجية (REST Endpoints)

### المصادقة (Auth):

* `POST /api/v1/login`: تسجيل الدخول، استرجاع الـ Bearer Token، بيانات المستخدم، وصلاحياته.

* `POST /api/v1/logout`: إبطال الـ Token.

* `GET /api/v1/profile`: معلومات الحساب الحالي والشركة التابعة.

### المشاريع (Projects):

* `GET /api/v1/projects`: قائمة المشاريع مع مؤشرات مختصرة للإنجاز.

* `POST /api/v1/projects`: إنشاء مشروع جديد.

* `GET /api/v1/projects/{id}`: تفاصيل المشروع والمقايسة التقديرية.

### سجل الموقع الميداني (Daily Reports):

* `GET /api/v1/projects/{projectId}/daily-reports`: سجل التقارير اليومية مع فلترة بالتاريخ.

* `POST /api/v1/projects/{projectId}/daily-reports`: إنشاء مسودة أو تقرير يومي جديد.

* `POST /api/v1/daily-reports/{id}/media`: رفع صور الموقع (Multipart Form Data مع استخراج بيانات الـ Geolocation).

### المخططات والوثائق (Drawings):

* `GET /api/v1/projects/{projectId}/documents`: تصفح المخططات مصنفة حسب الاختصاص مع إبراز النسخة المعتمدة (IFC).

* `POST /api/v1/projects/{projectId}/documents`: رفع إصدار جديد من مخطط معين.

### الطلبات الهندسية (Site Requests):

* `GET /api/v1/projects/{projectId}/requests`: استرجاع طلبات (WIR / RFI / Variation Orders).

* `POST /api/v1/projects/{projectId}/requests`: تقديم طلب استلام عمل أو أمر تغيير مع إرفاق التكلفة المقدرة.

* `PATCH /api/v1/requests/{id}/status`: تغيير الحالة (معتمد / مرفوض) من قِبل المدير أو الاستشاري.

### التقارير والتصدير (Exports & Pulse):

* `GET /api/v1/daily-reports/{id}/export-pdf`: توليد تقرير يومي رسمي بصيغة PDF يحتوي على شعار المقاول، حالة الطقس، العمالة، والصور مع التاريخ والوقت.

* `GET /api/v1/projects/{id}/executive-summary`: إحصائيات عامة سريعة للمالك (النسب المكتملة، عدد العمالة اليوم، المبالغ المحصلة).

## 6. متطلبات الواجهة وتجربة المستخدم (Vue 3 Frontend Specs)

1. **تصميم Mobile-First بالكامل:**

   * يجب أن تظهر الشاشات في المتصفح على الهواتف الذكية وكأنها تطبيق محلي (Native App Feel).

   * أزرار عريضة ملائمة للأصابع الكبيرة (مثل زر: "التقاط صورة للموقع"، "إرسال التقرير").

2. **شاشة التقرير اليومي السريع (Quick Daily Log Form):**

   * إدخال عددي سريع لحجم العمالة.

   * إمكانية فتح كاميرا الجوال مباشرة وحفظ الصورة في الواجهة مع إمكانية كتابة تعليق سريع على الصورة (مثال: "صب خرسانة القواعد").

3. **معرض صور الموقع (Site Visual Stream):**

   * عرض زمني تفاعلي (Timeline Stream) للصور اليومية مع وسم التاريخ تلقائيًا على الصورة.

4. **دعم العربية و RTL:**

   * الخط المستخدم: `Tajawal` أو `IBM Plex Sans Arabic`.

   * المحاذاة الافتراضية لليمين مع مراعاة الأرقام والمبالغ المالية بالريال السعودي.

## 7. خارطة التنفيذ التوجيهية للـ AI Coding Agent (Prompt Execution Plan)

قم بتنفيذ المشروع خطوة بخطوة بالترتيب التالي:

* **المهمة 1 (Scaffolding & Auth):**

  * إعداد مشروع Laravel 11 مع Sanctum وحزمة `spatie/laravel-permission`.

  * إنشاء جداول `tenants` و `users` مع تطبيق الـ Global Scope لعزل بيانات كل شركة.

  * إنشاء شاشة تسجيل الدخول في Vue 3 باستخدام Tailwind CSS و Pinia.

* **المهمة 2 (Projects & BOQ Engine):**

  * بناء جدول وعلاقات `projects` و `boq_items`.

  * تجهيز API استعراض وتحديث نسب إنجاز بنود المقايسة وحساب النسبة الكلية الموزونة للمشروع تلقائيًا.

  * بناء واجهة لوحة تحكم تعرض قائمة المشاريع وبطاقة كل مشروع مع شريط التقدم.

* **المهمة 3 (Daily Site Report & Media Upload):**

  * بناء جدول `daily_reports` و `daily_report_media`.

  * إعداد رفع الصور في Laravel وتوليد صور مصغرة (Thumbnails) عبر خلفية الـ Queue.

  * بناء واجهة إدخال التقرير اليومي المتوافقة مع الموبايل في Vue 3.

* **المهمة 4 (Site Requests & Revision Vault):**

  * بناء جداول `project_documents` مع منطق رقم الإصدار (Revision Numbering).

  * بناء جداول `site_requests` لإدارة طلبات الاستلام وأوامر التغيير (Variation Orders).

  * واجهة تبويب داخل صفحة المشروع لعرض المخططات والطلبات المفتوحة والمغلقة.

* **المهمة 5 (PDF Export & WhatsApp Share Link):**

  * بناء قالب Blade لتوليد ملف PDF أنيق للتقرير اليومي يحتوي على شعار المقاول، تفاصيل الطقس، العمالة، والصور المصغرة.

  * إنشاء زر في الواجهة "مشاركة عبر واتساب" يقوم بنسخ رابط فوري أو تجهيز رسالة مهيأة للمالك.