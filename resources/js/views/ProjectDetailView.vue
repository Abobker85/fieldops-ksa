<template>
  <div v-if="loading" class="text-center py-20">
    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-amber-500 border-t-transparent"></div>
    <p class="mt-2 text-sm text-slate-500">جاري تحميل بيانات العمليات الميدانية للمشروع...</p>
  </div>

  <div v-else-if="!project" class="text-center py-20">
    <h3 class="text-lg font-bold text-slate-800">المشروع غير موجود</h3>
    <button @click="$router.push('/')" class="mt-4 px-4 py-2 bg-amber-500 rounded-xl font-bold text-sm">العودة للرئيسية</button>
  </div>

  <div v-else class="space-y-6 pb-16">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
      <button @click="$router.push('/')" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
        <span>→ العودة للمشاريع</span>
      </button>

      <button
        @click="loadExecutiveSummary"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-xl text-xs font-bold transition shadow-sm"
      >
        <span>📊 نبض المالك (Executive Pulse)</span>
      </button>
    </div>

    <!-- Project Hero Card -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm relative overflow-hidden">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-1.5">
            <span class="px-2.5 py-0.5 bg-amber-50 text-amber-800 font-mono font-bold text-xs rounded-lg border border-amber-200">
              {{ project.code }}
            </span>
            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-lg border border-emerald-200">
              {{ project.status === 'active' ? 'موقع نشط' : project.status }}
            </span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ project.name }}</h1>
          <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
            <span>المالك: <strong class="text-slate-700">{{ project.client_name }}</strong></span>
            <span>•</span>
            <span>الاستشاري: <strong class="text-slate-700">{{ project.consultant_name || 'غير محدد' }}</strong></span>
            <span>•</span>
            <span>المدينة: <strong class="text-slate-700">{{ project.location_city }}</strong></span>
            <span>•</span>
            <span>قيمة العقد: <strong class="text-slate-900 font-bold">{{ formatCurrency(project.contract_value) }} ر.س</strong></span>
          </div>
        </div>

        <!-- Overall Weighted Progress Widget -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center gap-4 min-w-[240px]">
          <div class="relative w-16 h-16 flex items-center justify-center">
            <svg class="w-16 h-16 transform -rotate-90">
              <circle cx="32" cy="32" r="26" stroke="#e2e8f0" stroke-width="6" fill="transparent"/>
              <circle
                cx="32"
                cy="32"
                r="26"
                stroke="#f59e0b"
                stroke-width="6"
                fill="transparent"
                stroke-dasharray="163.36"
                :stroke-dashoffset="163.36 - (163.36 * (project.weighted_progress || 0)) / 100"
                stroke-linecap="round"
                class="transition-all duration-700"
              />
            </svg>
            <span class="absolute text-xs font-black text-slate-900">{{ project.weighted_progress || 0 }}%</span>
          </div>
          <div>
            <div class="text-[11px] font-bold text-slate-400 uppercase">نسبة الإنجاز الموزونة</div>
            <div class="text-sm font-black text-slate-900">محسوبة من المقايسة</div>
            <div class="text-[11px] text-amber-600 font-semibold">تحديث حي وميداني</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 text-sm font-bold whitespace-nowrap">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        class="px-4 py-2.5 rounded-xl transition flex items-center gap-1.5"
        :class="activeTab === tab.id ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white'"
      >
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== undefined"
          class="text-[11px] px-1.5 py-0.2 rounded-full font-bold"
          :class="activeTab === tab.id ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700'"
        >
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- TAB 1: BOQ ENGINE -->
    <div v-if="activeTab === 'boq'" class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-900">جدول الكميات والمقايسة التقديرية (BOQ)</h3>
          <p class="text-xs text-slate-500">حساب النسبة الكلية الموزونة للمشروع تلقائياً عند تعديل أي بند</p>
        </div>
        <button
          v-if="canManageBoq"
          @click="showAddBoqModal = true"
          class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition"
        >
          + إضافة بند مقايسة
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-right text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5">الكود</th>
                <th class="p-3.5">بيان الأعمال</th>
                <th class="p-3.5">الوحدة</th>
                <th class="p-3.5">الكمية</th>
                <th class="p-3.5">سعر الوحدة</th>
                <th class="p-3.5">الإجمالي (SAR)</th>
                <th class="p-3.5">الوزن النسبي</th>
                <th class="p-3.5 min-w-[200px]">نسبة الإنجاز الميداني</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in boqItems" :key="item.id" class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 font-mono font-bold text-slate-700">{{ item.item_code }}</td>
                <td class="p-3.5 font-semibold text-slate-900">{{ item.description }}</td>
                <td class="p-3.5 text-slate-600">{{ item.unit }}</td>
                <td class="p-3.5 text-slate-700 font-medium">{{ item.total_quantity }}</td>
                <td class="p-3.5 text-slate-700 font-medium">{{ item.unit_price }}</td>
                <td class="p-3.5 font-bold text-slate-900">{{ formatCurrency(item.total_price) }}</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-bold text-[11px]">
                    {{ item.weight_percentage }}%
                  </span>
                </td>
                <td class="p-3.5">
                  <div class="flex items-center gap-3">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      step="5"
                      :value="item.current_progress_percentage"
                      @change="updateItemProgress(item.id, $event.target.value)"
                      class="w-24 sm:w-28 accent-amber-500 cursor-pointer"
                    />
                    <span class="font-black text-amber-600 text-xs w-10 text-left">
                      {{ item.current_progress_percentage }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: DAILY SITE REPORTS & MEDIA -->
    <div v-if="activeTab === 'reports'" class="space-y-6">
      <!-- Quick Action Mobile Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
          @click="openQuickReportModal"
          class="flex items-center justify-center gap-2 py-4 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-2xl shadow-sm active:scale-98 transition text-sm sm:text-base"
        >
          <span>📝</span>
          <span>إدخال تقرير يومي سريع</span>
        </button>

        <button
          @click="openMediaUploadModal"
          class="flex items-center justify-center gap-2 py-4 px-4 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-2xl shadow-sm active:scale-98 transition text-sm sm:text-base"
        >
          <span>📸</span>
          <span>التقاط / رفع صور للموقع الميداني</span>
        </button>
      </div>

      <!-- Site Visual Stream (Timeline Photos) -->
      <div v-if="allPhotos.length > 0" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
        <h3 class="text-base font-bold text-slate-900 mb-1">المعرض الميداني الحي (Visual Stream)</h3>
        <p class="text-xs text-slate-500 mb-4">أحدث صور التوثيق الميداني المباشر من الموقع</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div
            v-for="photo in allPhotos"
            :key="photo.id"
            class="group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-square"
          >
            <img :src="photo.file_url || photo.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex flex-col justify-end p-2.5 text-white">
              <span class="text-[11px] font-bold line-clamp-1">{{ photo.caption || 'صورة موقع' }}</span>
              <span class="text-[9px] text-slate-300 font-mono">{{ formatDate(photo.captured_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Reports List -->
      <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-900">سجل التقارير الميدانية السابقة</h3>

        <div v-if="dailyReports.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200 p-6">
          <p class="text-sm text-slate-500">لا توجد تقارير مسجلة حتى الآن. ابدأ بإدخال تقرير اليوم!</p>
        </div>

        <div
          v-for="report in dailyReports"
          :key="report.id"
          class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <span class="font-bold text-base text-slate-900 font-mono">{{ report.report_date }}</span>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="report.status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                {{ report.status === 'approved' ? 'معتمد' : 'مقدم' }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <a
                :href="`/api/v1/daily-reports/${report.id}/export-pdf`"
                target="_blank"
                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1"
              >
                <span>📄</span>
                <span>تحميل PDF</span>
              </a>

              <button
                @click="shareOnWhatsApp(report)"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm"
              >
                <span>💬</span>
                <span>مشاركة واتساب</span>
              </button>
            </div>
          </div>

          <!-- Metadata Tags -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
            <div class="p-2.5 bg-slate-50 rounded-xl">
              <span class="text-slate-400 block text-[10px]">حجم العمالة:</span>
              <span class="font-black text-amber-700 text-sm">{{ report.manpower_count }} عامل وفني</span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl">
              <span class="text-slate-400 block text-[10px]">الطقس:</span>
              <span class="font-medium text-slate-800">{{ report.weather_condition || 'معتدل' }}</span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl">
              <span class="text-slate-400 block text-[10px]">المهندس المسجل:</span>
              <span class="font-medium text-slate-800">{{ report.user?.name || 'مهندس الموقع' }}</span>
            </div>
          </div>

          <!-- Summary & Blockers -->
          <div class="text-xs text-slate-700 space-y-2">
            <div>
              <strong class="text-slate-900 block mb-1">ملخص الأعمال اليومية:</strong>
              <p class="whitespace-pre-line bg-slate-50/50 p-3 rounded-xl border border-slate-100">{{ report.work_summary }}</p>
            </div>

            <div v-if="report.blockers_notes" class="bg-rose-50 border border-rose-100 text-rose-800 p-3 rounded-xl">
              <strong class="block mb-1 font-bold">⚠️ معوقات الموقع:</strong>
              <p class="whitespace-pre-line">{{ report.blockers_notes }}</p>
            </div>
          </div>

          <!-- Attached Photos in this report -->
          <div v-if="report.media && report.media.length > 0" class="flex gap-2 overflow-x-auto pt-2">
            <div v-for="m in report.media" :key="m.id" class="w-20 h-20 shrink-0 rounded-lg overflow-hidden border border-slate-200">
              <img :src="m.thumbnail_url || m.file_url" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: DRAWINGS & VAULT -->
    <div v-if="activeTab === 'drawings'" class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-900">مستودع المخططات والمستندات (Revision Vault)</h3>
          <p class="text-xs text-slate-500">المخططات المعتمدة للتنفيذ (IFC) وتتبع الإصدارات</p>
        </div>
        <button
          @click="showUploadDocModal = true"
          class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition"
        >
          + رفع مخطط جديد
        </button>
      </div>

      <!-- Filters -->
      <div class="flex gap-2 overflow-x-auto text-xs pb-1">
        <button
          v-for="d in disciplines"
          :key="d.id"
          @click="selectedDiscipline = d.id"
          class="px-3 py-1.5 rounded-xl font-medium transition"
          :class="selectedDiscipline === d.id ? 'bg-slate-800 text-white font-bold' : 'bg-white text-slate-600 border border-slate-200'"
        >
          {{ d.label }}
        </button>
      </div>

      <!-- Drawings Table -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-right text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5">الكود</th>
                <th class="p-3.5">عنوان المخطط</th>
                <th class="p-3.5">التخصص</th>
                <th class="p-3.5">رقم المراجعة</th>
                <th class="p-3.5">حالة الاعتماد</th>
                <th class="p-3.5">المستخدم</th>
                <th class="p-3.5 text-center">الإجراء</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 font-mono font-bold text-slate-700">{{ doc.document_code }}</td>
                <td class="p-3.5 font-semibold text-slate-900">{{ doc.title }}</td>
                <td class="p-3.5 text-slate-600">{{ translateDiscipline(doc.discipline) }}</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono font-bold text-[11px]">
                    {{ doc.current_revision }}
                  </span>
                </td>
                <td class="p-3.5">
                  <span
                    v-if="doc.is_approved_for_construction"
                    class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[11px]"
                  >
                    ✓ معتمد للبناء (IFC)
                  </span>
                  <span v-else class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-bold text-[11px]">
                    قيد الاعتماد
                  </span>
                </td>
                <td class="p-3.5 text-slate-600">{{ doc.uploader?.name || 'المهندس' }}</td>
                <td class="p-3.5 text-center">
                  <a
                    :href="doc.file_url"
                    target="_blank"
                    class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-xs font-bold transition inline-block"
                  >
                    عرض الملف
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 4: SITE REQUESTS (RFI & WIR & VARIATION ORDERS) -->
    <div v-if="activeTab === 'requests'" class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-900">الطلبات الهندسية والموقع (WIR / RFI / أومر التغيير)</h3>
          <p class="text-xs text-slate-500">استلام الأعمال الميدانية والاستفسارات الفنية</p>
        </div>
        <button
          @click="showCreateRequestModal = true"
          class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition"
        >
          + تقديم طلب جديد
        </button>
      </div>

      <!-- Requests Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="req in siteRequests"
          :key="req.id"
          class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3 flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded font-mono font-bold text-xs" :class="getRequestTypeBadge(req.type)">
                  {{ req.request_number }}
                </span>
                <span class="text-xs font-bold text-slate-500">{{ translateRequestType(req.type) }}</span>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="getRequestStatusBadge(req.status)">
                {{ translateStatus(req.status) }}
              </span>
            </div>

            <h4 class="font-bold text-slate-900 text-sm mt-2">{{ req.title }}</h4>
            <p class="text-xs text-slate-600 mt-1 whitespace-pre-line">{{ req.description }}</p>

            <div v-if="req.location_details" class="mt-2 text-[11px] text-slate-500">
              📍 الموقع: <span class="font-medium text-slate-700">{{ req.location_details }}</span>
            </div>

            <div v-if="req.type === 'VARIATION_ORDER' && req.estimated_cost_impact > 0" class="mt-2 text-xs font-bold text-slate-900 bg-amber-50 p-2 rounded-lg border border-amber-200">
              التكلفة التقديرية للأمر: {{ formatCurrency(req.estimated_cost_impact) }} ر.س
            </div>

            <div v-if="req.response_notes" class="mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
              <span class="font-bold text-slate-700 block">ملاحظات الاستشاري / الإدارة:</span>
              <p class="text-slate-600 mt-0.5">{{ req.response_notes }}</p>
            </div>
          </div>

          <!-- Status Action for PM/Consultant/Owner -->
          <div v-if="canApproveRequests && req.status === 'pending'" class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              @click="updateRequestStatus(req.id, 'approved')"
              class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition"
            >
              ✓ اعتماد الطلب
            </button>
            <button
              @click="updateRequestStatus(req.id, 'rejected')"
              class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition"
            >
              ✕ رفض
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: CLAIMS -->
    <div v-if="activeTab === 'claims'" class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-900">المستخلصات المالية للمشروع (Payment Claims)</h3>
          <p class="text-xs text-slate-500">تتبع المستخلصات الدورية وضريبة القيمة المضافة 15%</p>
        </div>
        <button
          @click="showCreateClaimModal = true"
          class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition"
        >
          + رفع مستخلص جديد
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-right text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5">رقم المستخلص</th>
                <th class="p-3.5">الفترة</th>
                <th class="p-3.5">المبلغ المطالب به</th>
                <th class="p-3.5">ضريبة القيمة المضافة (15%)</th>
                <th class="p-3.5">الإجمالي شاملاً الضريبة</th>
                <th class="p-3.5">المبلغ المعتمد</th>
                <th class="p-3.5">الحالة</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="claim in paymentClaims" :key="claim.id" class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 font-mono font-bold text-slate-900">{{ claim.claim_number }}</td>
                <td class="p-3.5 text-slate-600">{{ claim.period_start }} إلى {{ claim.period_end }}</td>
                <td class="p-3.5 font-semibold text-slate-800">{{ formatCurrency(claim.claimed_amount) }} ر.س</td>
                <td class="p-3.5 text-slate-600">{{ formatCurrency(claim.vat_amount) }} ر.س</td>
                <td class="p-3.5 font-bold text-slate-900">{{ formatCurrency(claim.total_claimed_with_vat) }} ر.س</td>
                <td class="p-3.5 font-bold text-emerald-700">
                  {{ claim.approved_amount ? `${formatCurrency(claim.approved_amount)} ر.س` : '-' }}
                </td>
                <td class="p-3.5">
                  <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px]" :class="getClaimStatusBadge(claim.status)">
                    {{ translateClaimStatus(claim.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quick Daily Report Modal -->
    <div v-if="showQuickReportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" dir="rtl">
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">إدخال التقرير الميداني اليومي</h3>
          <button @click="showQuickReportModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submitDailyReport" class="space-y-4 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-slate-700 mb-1">تاريخ التقرير</label>
              <input v-model="reportForm.report_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-medium text-slate-700 mb-1">عدد العمالة الميدانية</label>
              <input v-model.number="reportForm.manpower_count" type="number" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block font-medium text-slate-700 mb-1">حالة الطقس ودرجة الحرارة</label>
            <input v-model="reportForm.weather_condition" placeholder="مشمس / 34°م" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
          </div>

          <div>
            <label class="block font-medium text-slate-700 mb-1">ملخص الأعمال اليومية المنفذة</label>
            <textarea v-model="reportForm.work_summary" required rows="4" placeholder="اكتب ما تم إنجازه اليوم بالتفصيل..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
          </div>

          <div>
            <label class="block font-medium text-slate-700 mb-1">معوقات وملاحظات الموقع (اختياري)</label>
            <textarea v-model="reportForm.blockers_notes" rows="2" placeholder="أي تأخير أو عوائق في التوريد أو الموقع..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showQuickReportModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-medium">إلغاء</button>
            <button type="submit" :disabled="submittingReport" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50">
              <span v-if="submittingReport">جاري الحفظ...</span>
              <span v-else>حفظ التقرير وإرساله</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Upload Modal (with geolocation) -->
    <div v-if="showMediaUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" dir="rtl">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">رفع صورة للموقع الميداني</h3>
          <button @click="showMediaUploadModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submitMediaUpload" class="space-y-4 mt-4 text-sm">
          <div>
            <label class="block font-medium text-slate-700 mb-1">حدد التقرير اليومي المرتبط</label>
            <select v-model="mediaForm.reportId" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
              <option v-for="rep in dailyReports" :key="rep.id" :value="rep.id">
                تقرير يوم: {{ rep.report_date }}
              </option>
            </select>
          </div>

          <div>
            <label class="block font-medium text-slate-700 mb-1">ملف الصورة / التقاط بالكاميرا</label>
            <input type="file" accept="image/*" capture="environment" @change="onFileSelected" required class="w-full text-xs text-slate-500 file:mr-0 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100" />
          </div>

          <div>
            <label class="block font-medium text-slate-700 mb-1">تعليق توضيحي على الصورة</label>
            <input v-model="mediaForm.caption" placeholder="مثال: صب خرسانة القواعد المسلحة" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
          </div>

          <div v-if="gpsCoords.lat" class="p-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs flex items-center gap-1.5">
            <span>📍 تم التقاط إحداثيات الموقع بنجاح: {{ gpsCoords.lat }}, {{ gpsCoords.lng }}</span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showMediaUploadModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-medium">إلغاء</button>
            <button type="submit" :disabled="uploadingMedia" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl font-bold transition disabled:opacity-50">
              <span v-if="uploadingMedia">جاري الرفع...</span>
              <span v-else>رفع وأرشفة الصورة</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Executive Summary Pulse Modal -->
    <div v-if="showPulseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" dir="rtl">
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="text-xl">📊</span>
            <h3 class="text-lg font-bold text-slate-900">الملخص التنفيذي ونبض المشروع</h3>
          </div>
          <button @click="showPulseModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div v-if="pulseData" class="mt-4 space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl">
              <span class="text-xs text-amber-800 block">نسبة الإنجاز الفعلية:</span>
              <span class="text-2xl font-black text-amber-900">{{ pulseData.weighted_progress }}%</span>
            </div>
            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl">
              <span class="text-xs text-slate-500 block">عمالة اليوم / الأحدث:</span>
              <span class="text-2xl font-black text-slate-900">{{ pulseData.today_or_latest_manpower }} فرد</span>
            </div>
            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl">
              <span class="text-xs text-slate-500 block">إجمالي المطالبات المرفوعة:</span>
              <span class="text-lg font-black text-slate-900">{{ formatCurrency(pulseData.total_claimed) }} ر.س</span>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl">
              <span class="text-xs text-emerald-800 block">المبالغ المعتمدة:</span>
              <span class="text-lg font-black text-emerald-900">{{ formatCurrency(pulseData.total_approved_claims) }} ر.س</span>
            </div>
          </div>

          <div v-if="pulseData.latest_report" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-xs">
            <span class="font-bold text-slate-800 block">آخر تقرير ميداني ({{ pulseData.latest_report.report_date }}):</span>
            <p class="text-slate-600 whitespace-pre-line">{{ pulseData.latest_report.work_summary }}</p>
          </div>

          <div class="pt-3 border-t border-slate-100 text-center">
            <button @click="showPulseModal = false" class="w-full py-2.5 bg-slate-900 text-white rounded-xl font-bold">إغلاق</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const authStore = useAuthStore();
const projectId = route.params.id;

const loading = ref(true);
const project = ref(null);
const activeTab = ref('reports');

const boqItems = ref([]);
const dailyReports = ref([]);
const projectDocuments = ref([]);
const siteRequests = ref([]);
const paymentClaims = ref([]);

const showQuickReportModal = ref(false);
const showMediaUploadModal = ref(false);
const showAddBoqModal = ref(false);
const showUploadDocModal = ref(false);
const showCreateRequestModal = ref(false);
const showCreateClaimModal = ref(false);
const showPulseModal = ref(false);

const pulseData = ref(null);
const submittingReport = ref(false);
const uploadingMedia = ref(false);
const selectedDiscipline = ref('all');
const gpsCoords = ref({ lat: null, lng: null });

const reportForm = ref({
  report_date: new Date().toISOString().split('T')[0],
  weather_condition: 'مشمس / 34°م',
  manpower_count: 25,
  work_summary: '',
  blockers_notes: '',
  status: 'submitted',
});

const mediaForm = ref({
  reportId: null,
  file: null,
  caption: '',
});

const disciplines = [
  { id: 'all', label: 'الكل' },
  { id: 'architectural', label: 'معماري' },
  { id: 'structural', label: 'إنشائي' },
  { id: 'mechanical', label: 'ميكانيكا' },
  { id: 'electrical', label: 'كهرباء' },
];

const tabs = computed(() => [
  { id: 'reports', label: 'اليوميات الميدانية', icon: '📝', count: dailyReports.value.length },
  { id: 'boq', label: 'المقايسة والإنجاز', icon: '📐', count: boqItems.value.length },
  { id: 'drawings', label: 'المخططات والوثائق', icon: '📁', count: projectDocuments.value.length },
  { id: 'requests', label: 'الطلبات الهندسية', icon: '📨', count: siteRequests.value.length },
  { id: 'claims', label: 'المستخلصات', icon: '💰', count: paymentClaims.value.length },
]);

const canManageBoq = computed(() => ['owner', 'pm'].includes(authStore.userRole));
const canApproveRequests = computed(() => ['owner', 'pm', 'viewer'].includes(authStore.userRole));

const allPhotos = computed(() => {
  const photos = [];
  dailyReports.value.forEach(r => {
    if (r.media) {
      r.media.forEach(m => photos.push(m));
    }
  });
  return photos;
});

const filteredDocuments = computed(() => {
  if (selectedDiscipline.value === 'all') return projectDocuments.value;
  return projectDocuments.value.filter(d => d.discipline === selectedDiscipline.value);
});

const fetchProjectDetails = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/projects/${projectId}`);
    project.value = res.data.data;
    boqItems.value = project.value.boq_items || [];
    dailyReports.value = project.value.daily_reports || [];
    projectDocuments.value = project.value.documents || [];
    siteRequests.value = project.value.site_requests || [];
    paymentClaims.value = project.value.payment_claims || [];
  } catch (err) {
    console.error('Failed to load project details:', err);
  } finally {
    loading.value = false;
  }
};

const updateItemProgress = async (itemId, newProgress) => {
  try {
    const res = await api.patch(`/boq/${itemId}/progress`, {
      current_progress_percentage: parseFloat(newProgress),
    });
    project.value.weighted_progress = res.data.data.project_weighted_progress;
    const item = boqItems.value.find(i => i.id === itemId);
    if (item) {
      item.current_progress_percentage = parseFloat(newProgress);
    }
  } catch (err) {
    alert('فشل تحديث نسبة الإنجاز');
  }
};

const openQuickReportModal = () => {
  showQuickReportModal.value = true;
};

const submitDailyReport = async () => {
  submittingReport.value = true;
  try {
    await api.post(`/projects/${projectId}/daily-reports`, reportForm.value);
    showQuickReportModal.value = false;
    await fetchProjectDetails();
  } catch (err) {
    alert(err.response?.data?.message || 'فشل حفظ التقرير اليومي');
  } finally {
    submittingReport.value = false;
  }
};

const openMediaUploadModal = () => {
  if (dailyReports.value.length === 0) {
    alert('يرجى تسجيل تقرير يومي أولاً لربط الصور به.');
    return;
  }
  mediaForm.value.reportId = dailyReports.value[0].id;

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        gpsCoords.value = {
          lat: pos.coords.latitude.toFixed(6),
          lng: pos.coords.longitude.toFixed(6),
        };
      },
      () => {},
      { timeout: 5000 }
    );
  }

  showMediaUploadModal.value = true;
};

const onFileSelected = (e) => {
  mediaForm.value.file = e.target.files[0];
};

const submitMediaUpload = async () => {
  if (!mediaForm.value.file) return;
  uploadingMedia.value = true;

  const fd = new FormData();
  fd.append('file', mediaForm.value.file);
  if (mediaForm.value.caption) fd.append('caption', mediaForm.value.caption);
  if (gpsCoords.value.lat) {
    fd.append('geo_latitude', gpsCoords.value.lat);
    fd.append('geo_longitude', gpsCoords.value.lng);
  }

  try {
    await api.post(`/daily-reports/${mediaForm.value.reportId}/media`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    showMediaUploadModal.value = false;
    mediaForm.value.caption = '';
    await fetchProjectDetails();
  } catch (err) {
    alert(err.response?.data?.message || 'فشل رفع الصورة');
  } finally {
    uploadingMedia.value = false;
  }
};

const shareOnWhatsApp = (report) => {
  const text = `*تقرير الموقع الميداني اليومي - ${project.value.name} (${project.value.code})*
📅 *التاريخ:* ${report.report_date}
👷 *العمالة الميدانية:* ${report.manpower_count} عامل
🌤 *الطقس:* ${report.weather_condition || 'معتدل'}
📊 *نسبة إنجاز المشروع:* ${project.value.weighted_progress}%

*ملخص الأعمال المنفذة:*
${report.work_summary}
${report.blockers_notes ? `\n*المعوقات والملاحظات:*\n${report.blockers_notes}` : ''}

🔗 يمكنك تحميل التقرير الرسمي المعتمد PDF من الرابط:
${window.location.origin}/api/v1/daily-reports/${report.id}/export-pdf`;

  const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
  window.open(url, '_blank');
};

const loadExecutiveSummary = async () => {
  try {
    const res = await api.get(`/projects/${projectId}/executive-summary`);
    pulseData.value = res.data.data;
    showPulseModal.value = true;
  } catch (err) {
    alert('فشل تحميل الملخص التنفيذي');
  }
};

const updateRequestStatus = async (id, status) => {
  const note = prompt('أدخل ملاحظات الاعتماد / الرفض إن وجدت:');
  try {
    await api.patch(`/requests/${id}/status`, { status, response_notes: note });
    await fetchProjectDetails();
  } catch (err) {
    alert('فشل تحديث حالة الطلب');
  }
};

const translateDiscipline = (d) => {
  const map = {
    architectural: 'معماري',
    structural: 'إنشائي',
    mechanical: 'ميكانيكا',
    electrical: 'كهرباء',
    contracts: 'عقود',
    permits: 'تراخيص',
  };
  return map[d] || d;
};

const translateRequestType = (t) => {
  const map = {
    WIR: 'طلب استلام أعمال (WIR)',
    RFI: 'طلب استفسار فني (RFI)',
    VARIATION_ORDER: 'أمر تغيير (Variation Order)',
  };
  return map[t] || t;
};

const translateStatus = (s) => {
  const map = {
    pending: 'قيد الانتظار',
    approved: 'معتمد',
    rejected: 'مرفوض',
    under_review: 'قيد المراجعة',
  };
  return map[s] || s;
};

const translateClaimStatus = (s) => {
  const map = {
    submitted: 'مقدم',
    certified: 'معتمد للصرف',
    paid_partially: 'مصروف جزئياً',
    paid: 'مدفوع بالكامل',
  };
  return map[s] || s;
};

const getRequestTypeBadge = (t) => {
  return t === 'VARIATION_ORDER' ? 'bg-purple-100 text-purple-800' : (t === 'WIR' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800');
};

const getRequestStatusBadge = (s) => {
  return s === 'approved' ? 'bg-emerald-50 text-emerald-700' : (s === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700');
};

const getClaimStatusBadge = (s) => {
  return s === 'paid' ? 'bg-emerald-100 text-emerald-800' : (s === 'certified' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800');
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('ar-SA', { maximumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
};

onMounted(fetchProjectDetails);
</script>
