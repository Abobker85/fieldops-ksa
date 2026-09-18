<template>
  <div v-if="loading" class="text-center py-20">
    <div class="inline-block animate-spin rounded-full h-8 w-8 border-3 border-amber-500 border-t-transparent"></div>
    <p class="mt-2 text-xs text-slate-500">{{ localeStore.t('detail.loading') }}</p>
  </div>

  <div v-else-if="!project" class="text-center py-20 bg-white rounded-2xl border border-slate-200 p-8 shadow-xs">
    <h3 class="text-base font-bold text-slate-800">{{ localeStore.t('detail.notFound') }}</h3>
    <button @click="$router.push('/')" class="mt-4 px-4 py-2 bg-amber-500 hover:bg-amber-400 rounded-xl font-bold text-xs text-slate-950 transition shadow-xs">
      {{ localeStore.t('detail.backHome') }}
    </button>
  </div>

  <div v-else class="space-y-6 pb-16">
    <!-- Breadcrumb & Top Actions -->
    <div class="flex items-center justify-between">
      <button
        @click="$router.push('/')"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition group"
      >
        <component :is="localeStore.isRtl ? ArrowRight : ArrowLeft" class="w-4 h-4 transition-transform group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
        <span>{{ localeStore.t('detail.backToProjects') }}</span>
      </button>

      <button
        @click="loadExecutiveSummary"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-amber-400 rounded-xl text-xs font-bold transition shadow-xs"
      >
        <Activity class="w-3.5 h-3.5" />
        <span>{{ localeStore.t('detail.executivePulse') }}</span>
      </button>
    </div>

    <!-- Project Hero Card -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="px-2.5 py-0.5 bg-amber-50 text-amber-900 font-mono font-bold text-xs rounded-md border border-amber-200/80">
              {{ project.code }}
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
              :class="project.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-slate-100 text-slate-600 border border-slate-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="project.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
              {{ project.status === 'active' ? localeStore.t('detail.activeSite') : localeStore.translateStatus(project.status) }}
            </span>
          </div>

          <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ project.name }}</h1>

          <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-500">
            <span>
              <span class="text-slate-400">{{ localeStore.t('detail.client') }}</span>
              <strong class="text-slate-700 ms-1">{{ project.client_name }}</strong>
            </span>
            <span class="text-slate-300">•</span>
            <span>
              <span class="text-slate-400">{{ localeStore.t('detail.consultant') }}</span>
              <strong class="text-slate-700 ms-1">{{ project.consultant_name || localeStore.t('projects.notSpecified') }}</strong>
            </span>
            <span class="text-slate-300">•</span>
            <span>
              <span class="text-slate-400">{{ localeStore.t('detail.city') }}</span>
              <strong class="text-slate-700 ms-1">{{ project.location_city }}</strong>
            </span>
            <span class="text-slate-300">•</span>
            <span>
              <span class="text-slate-400">{{ localeStore.t('detail.contractValue') }}</span>
              <strong class="text-slate-900 font-bold ms-1">{{ formatCurrency(project.contract_value) }}</strong>
            </span>
          </div>
        </div>

        <!-- Overall Weighted Progress Widget -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 flex items-center gap-4 min-w-[240px]">
          <div class="relative w-16 h-16 shrink-0 flex items-center justify-center">
            <svg class="w-16 h-16 transform -rotate-90">
              <circle cx="32" cy="32" r="26" stroke="#e2e8f0" stroke-width="5" fill="transparent"/>
              <circle
                cx="32"
                cy="32"
                r="26"
                stroke="#f59e0b"
                stroke-width="5"
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
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
              {{ localeStore.t('detail.weightedProgressLabel') }}
            </div>
            <div class="text-xs font-black text-slate-900 mt-0.5">
              {{ localeStore.t('detail.calculatedFromBoq') }}
            </div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">
              {{ localeStore.t('detail.liveFieldUpdate') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1 overflow-x-auto pb-1 border-b border-slate-200 text-xs sm:text-sm font-semibold whitespace-nowrap">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        class="px-4 py-2.5 rounded-xl transition flex items-center gap-2"
        :class="activeTab === tab.id ? 'bg-slate-900 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white'"
      >
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== undefined"
          class="text-[11px] px-2 py-0.5 rounded-full font-bold transition"
          :class="activeTab === tab.id ? 'bg-amber-400 text-slate-950' : 'bg-slate-100 text-slate-600'"
        >
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- TAB 1: BOQ ENGINE -->
    <div v-if="activeTab === 'boq'" class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.boq.title') }}</h3>
          <p class="text-xs text-slate-500">{{ localeStore.t('detail.boq.subtitle') }}</p>
        </div>
        <button
          v-if="canManageBoq"
          @click="showAddBoqModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ localeStore.t('detail.boq.addItem') }}</span>
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.code') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.description') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.unit') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.quantity') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.unitPrice') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.total') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.boq.weight') }}</th>
                <th class="p-3.5 text-start min-w-[200px]">{{ localeStore.t('detail.boq.fieldProgress') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in boqItems" :key="item.id" class="hover:bg-slate-50/70 transition">
                <td class="p-3.5 font-mono font-bold text-slate-700 text-start">{{ item.item_code }}</td>
                <td class="p-3.5 font-semibold text-slate-900 text-start">{{ item.description }}</td>
                <td class="p-3.5 text-slate-600 text-start">{{ translateBoqUnit(item.unit) }}</td>
                <td class="p-3.5 text-slate-700 font-medium text-start">{{ localeStore.formatNumber(item.total_quantity) }}</td>
                <td class="p-3.5 text-slate-700 font-medium text-start">{{ localeStore.formatNumber(item.unit_price) }}</td>
                <td class="p-3.5 font-bold text-slate-900 text-start">{{ formatCurrency(item.total_price) }}</td>
                <td class="p-3.5 text-start">
                  <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-bold text-[11px] border border-slate-200/60">
                    {{ item.weight_percentage }}%
                  </span>
                </td>
                <td class="p-3.5 text-start">
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
                    <span class="font-black text-amber-600 text-xs w-10 text-start">
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
      <!-- Quick Action Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
          @click="openQuickReportModal"
          class="flex items-center justify-center gap-2.5 py-3.5 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-2xl shadow-xs active:scale-98 transition text-sm"
        >
          <ClipboardList class="w-4 h-4" />
          <span>{{ localeStore.t('detail.reports.quickReport') }}</span>
        </button>

        <button
          @click="openMediaUploadModal"
          class="flex items-center justify-center gap-2.5 py-3.5 px-4 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-2xl shadow-xs active:scale-98 transition text-sm"
        >
          <Camera class="w-4 h-4" />
          <span>{{ localeStore.t('detail.reports.captureMedia') }}</span>
        </button>
      </div>

      <!-- Site Visual Stream (Timeline Photos) -->
      <div v-if="allPhotos.length > 0" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-0.5">{{ localeStore.t('detail.reports.visualStreamTitle') }}</h3>
        <p class="text-xs text-slate-500 mb-4">{{ localeStore.t('detail.reports.visualStreamSubtitle') }}</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div
            v-for="photo in allPhotos"
            :key="photo.id"
            class="group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-square"
          >
            <img :src="photo.file_url || photo.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent flex flex-col justify-end p-2.5 text-white">
              <span class="text-[11px] font-bold line-clamp-1">{{ photo.caption || localeStore.t('detail.reports.sitePhoto') }}</span>
              <span class="text-[9px] text-slate-300 font-mono">{{ localeStore.formatTime(photo.captured_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Reports List -->
      <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.reports.historyTitle') }}</h3>

        <div v-if="dailyReports.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
          <p class="text-xs sm:text-sm text-slate-500">{{ localeStore.t('detail.reports.empty') }}</p>
        </div>

        <div
          v-for="report in dailyReports"
          :key="report.id"
          class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <span class="font-bold text-base text-slate-900 font-mono">{{ report.report_date }}</span>
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
                :class="report.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-amber-50 text-amber-700 border border-amber-200/70'"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="report.status === 'approved' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                {{ report.status === 'approved' ? localeStore.t('statuses.approved') : localeStore.t('statuses.submitted') }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="canManageBoq && report.status !== 'approved'"
                @click="approveDailyReport(report.id)"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
              >
                <Check class="w-3.5 h-3.5" />
                <span>{{ localeStore.t('detail.reports.approve') }}</span>
              </button>

              <a
                :href="report.export_url || `/api/v1/daily-reports/${report.id}/export-pdf?share_token=${report.share_token || ''}`"
                target="_blank"
                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5"
              >
                <Download class="w-3.5 h-3.5" />
                <span>{{ localeStore.t('detail.reports.downloadPdf') }}</span>
              </a>

              <button
                @click="shareOnWhatsApp(report)"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
              >
                <Share2 class="w-3.5 h-3.5" />
                <span>{{ localeStore.t('detail.reports.shareWhatsApp') }}</span>
              </button>
            </div>
          </div>

          <!-- Metadata Tags -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
            <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-medium">{{ localeStore.t('detail.reports.manpower') }}</span>
              <span class="font-black text-amber-800 text-sm">
                {{ report.manpower_count }} {{ localeStore.t('detail.reports.workersUnit') }}
              </span>
            </div>
            <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-medium">{{ localeStore.t('detail.reports.weather') }}</span>
              <span class="font-semibold text-slate-800">{{ report.weather_condition || localeStore.t('detail.reports.weatherDefault') }}</span>
            </div>
            <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-medium">{{ localeStore.t('detail.reports.engineer') }}</span>
              <span class="font-semibold text-slate-800 truncate block">{{ report.user?.name || localeStore.t('roles.site_engineer') }}</span>
            </div>
          </div>

          <!-- Summary & Blockers -->
          <div class="text-xs text-slate-700 space-y-2">
            <div>
              <strong class="text-slate-900 block mb-1 font-bold">{{ localeStore.t('detail.reports.workSummary') }}</strong>
              <p class="whitespace-pre-line bg-slate-50/60 p-3 rounded-xl border border-slate-100 text-slate-700">{{ report.work_summary }}</p>
            </div>

            <div v-if="report.blockers_notes" class="bg-rose-50/70 border border-rose-200/60 text-rose-900 p-3 rounded-xl">
              <strong class="block mb-1 font-bold flex items-center gap-1.5 text-rose-800">
                <AlertCircle class="w-3.5 h-3.5 text-rose-600" />
                <span>{{ localeStore.t('detail.reports.blockers') }}</span>
              </strong>
              <p class="whitespace-pre-line text-xs text-rose-800">{{ report.blockers_notes }}</p>
            </div>
          </div>

          <!-- Attached Photos in this report -->
          <div v-if="report.media && report.media.length > 0" class="flex gap-2 overflow-x-auto pt-1">
            <div v-for="m in report.media" :key="m.id" class="w-16 h-16 shrink-0 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
              <img :src="m.thumbnail_url || m.file_url" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: DRAWINGS & VAULT -->
    <div v-if="activeTab === 'drawings'" class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.drawings.title') }}</h3>
          <p class="text-xs text-slate-500">{{ localeStore.t('detail.drawings.subtitle') }}</p>
        </div>
        <button
          v-if="canManageDocs"
          @click="showUploadDocModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ localeStore.t('detail.drawings.uploadBtn') }}</span>
        </button>
      </div>

      <!-- Filters -->
      <div class="flex gap-1.5 overflow-x-auto text-xs pb-1">
        <button
          v-for="d in disciplines"
          :key="d.id"
          @click="selectedDiscipline = d.id"
          class="px-3 py-1.5 rounded-xl font-medium transition"
          :class="selectedDiscipline === d.id ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
        >
          {{ d.label }}
        </button>
      </div>

      <!-- Drawings Table -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.code') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.titleCol') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.discipline') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.revision') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.status') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.drawings.user') }}</th>
                <th class="p-3.5 text-center">{{ localeStore.t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-slate-50/70 transition">
                <td class="p-3.5 font-mono font-bold text-slate-700 text-start">{{ doc.document_code }}</td>
                <td class="p-3.5 font-semibold text-slate-900 text-start">{{ doc.title }}</td>
                <td class="p-3.5 text-slate-600 text-start">{{ localeStore.translateDiscipline(doc.discipline) }}</td>
                <td class="p-3.5 text-start">
                  <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-mono font-bold text-[11px] border border-slate-200/60">
                    {{ doc.current_revision }}
                  </span>
                </td>
                <td class="p-3.5 text-start">
                  <span
                    v-if="doc.is_approved_for_construction"
                    class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/70 rounded-full font-semibold text-[11px] inline-flex items-center gap-1"
                  >
                    <Check class="w-3 h-3" />
                    <span>{{ localeStore.t('detail.drawings.ifcApproved') }}</span>
                  </span>
                  <span v-else class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200/70 rounded-full font-semibold text-[11px]">
                    {{ localeStore.t('detail.drawings.pendingApproval') }}
                  </span>
                </td>
                <td class="p-3.5 text-slate-600 text-start">{{ doc.uploader?.name || localeStore.t('roles.site_engineer') }}</td>
                <td class="p-3.5 text-center">
                  <a
                    :href="doc.file_url"
                    target="_blank"
                    class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-900 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 border border-amber-200/50"
                  >
                    <span>{{ localeStore.t('common.viewFile') }}</span>
                    <ExternalLink class="w-3 h-3" />
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
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.requests.title') }}</h3>
          <p class="text-xs text-slate-500">{{ localeStore.t('detail.requests.subtitle') }}</p>
        </div>
        <button
          v-if="canSubmitRequests"
          @click="showCreateRequestModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ localeStore.t('detail.requests.submitBtn') }}</span>
        </button>
      </div>

      <!-- Requests Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="req in siteRequests"
          :key="req.id"
          class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between hover:border-slate-300 transition"
        >
          <div>
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded font-mono font-bold text-xs" :class="getRequestTypeBadge(req.type)">
                  {{ req.request_number }}
                </span>
                <span class="text-xs font-bold text-slate-500">{{ localeStore.translateRequestType(req.type) }}</span>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getRequestStatusBadge(req.status)">
                {{ localeStore.translateStatus(req.status) }}
              </span>
            </div>

            <h4 class="font-bold text-slate-900 text-sm mt-2.5">{{ req.title }}</h4>
            <p class="text-xs text-slate-600 mt-1 whitespace-pre-line">{{ req.description }}</p>

            <div v-if="req.location_details" class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
              <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
              <span>{{ localeStore.t('detail.requests.location') }} <strong class="font-medium text-slate-700">{{ req.location_details }}</strong></span>
            </div>

            <div v-if="req.type === 'VARIATION_ORDER' && req.estimated_cost_impact > 0" class="mt-2 text-xs font-bold text-slate-900 bg-amber-50/70 p-2.5 rounded-xl border border-amber-200/70">
              {{ localeStore.t('detail.requests.costImpact') }} {{ formatCurrency(req.estimated_cost_impact) }}
            </div>

            <div v-if="req.response_notes" class="mt-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
              <span class="font-bold text-slate-700 block text-[11px]">{{ localeStore.t('detail.requests.notes') }}</span>
              <p class="text-slate-600 mt-0.5">{{ req.response_notes }}</p>
            </div>
          </div>

          <!-- Status Action for PM/Consultant/Owner -->
          <div v-if="canApproveRequests && req.status === 'pending'" class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              @click="openReviewModal(req, 'approved')"
              class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-xs"
            >
              <Check class="w-3.5 h-3.5" />
              <span>{{ localeStore.t('detail.requests.approveBtn') }}</span>
            </button>
            <button
              @click="openReviewModal(req, 'rejected')"
              class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-xs"
            >
              <X class="w-3.5 h-3.5" />
              <span>{{ localeStore.t('detail.requests.rejectBtn') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: CLAIMS -->
    <div v-if="activeTab === 'claims'" class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.claims.title') }}</h3>
          <p class="text-xs text-slate-500">{{ localeStore.t('detail.claims.subtitle') }}</p>
        </div>
        <button
          v-if="canManageBoq"
          @click="showCreateClaimModal = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition self-start sm:self-auto"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ localeStore.t('detail.claims.newBtn') }}</span>
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.claimNumber') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.period') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.claimedAmount') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.vatAmount') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.totalWithVat') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.approvedAmount') }}</th>
                <th class="p-3.5 text-start">{{ localeStore.t('detail.claims.status') }}</th>
                <th class="p-3.5 text-center">{{ localeStore.t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="claim in paymentClaims" :key="claim.id" class="hover:bg-slate-50/70 transition">
                <td class="p-3.5 font-mono font-bold text-slate-900 text-start">{{ claim.claim_number }}</td>
                <td class="p-3.5 text-slate-600 text-start">{{ claim.period_start }} {{ localeStore.t('detail.claims.to') }} {{ claim.period_end }}</td>
                <td class="p-3.5 font-semibold text-slate-800 text-start">{{ formatCurrency(claim.claimed_amount) }}</td>
                <td class="p-3.5 text-slate-600 text-start">{{ formatCurrency(claim.vat_amount) }}</td>
                <td class="p-3.5 font-bold text-slate-900 text-start">{{ formatCurrency(claim.total_claimed_with_vat) }}</td>
                <td class="p-3.5 font-bold text-emerald-700 text-start">
                  {{ claim.approved_amount ? formatCurrency(claim.approved_amount) : '-' }}
                </td>
                <td class="p-3.5 text-start">
                  <span class="px-2.5 py-0.5 rounded-full font-semibold text-[11px]" :class="getClaimStatusBadge(claim.status)">
                    {{ localeStore.translateClaimStatus(claim.status) }}
                  </span>
                </td>
                <td class="p-3.5 text-center">
                  <div v-if="canManageBoq" class="flex items-center justify-center gap-1.5">
                    <button
                      v-if="claim.status === 'submitted'"
                      @click="updateClaimStatusAction(claim.id, 'certified')"
                      class="px-2.5 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200/80 rounded-lg font-bold text-[11px] transition"
                    >
                      {{ localeStore.t('detail.claims.certifyBtn') }}
                    </button>
                    <button
                      v-else-if="claim.status === 'certified'"
                      @click="updateClaimStatusAction(claim.id, 'paid')"
                      class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 rounded-lg font-bold text-[11px] transition"
                    >
                      {{ localeStore.t('detail.claims.recordPaidBtn') }}
                    </button>
                    <span v-else class="text-slate-400 text-[11px]">-</span>
                  </div>
                  <span v-else class="text-slate-400 text-[11px]">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quick Daily Report Modal -->
    <div
      v-if="showQuickReportModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.reportModal.title') }}</h3>
          <button @click="showQuickReportModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitDailyReport" class="space-y-3.5 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.reportModal.date') }}</label>
              <input v-model="reportForm.report_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.reportModal.manpower') }}</label>
              <input v-model.number="reportForm.manpower_count" type="number" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.reportModal.weather') }}</label>
            <input v-model="reportForm.weather_condition" :placeholder="localeStore.isRtl ? 'مشمس / 34°م' : 'Sunny / 34°C'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.reportModal.summary') }}</label>
            <textarea v-model="reportForm.work_summary" required rows="4" :placeholder="localeStore.t('detail.reportModal.summaryPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm"></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.reportModal.blockers') }}</label>
            <textarea v-model="reportForm.blockers_notes" rows="2" :placeholder="localeStore.t('detail.reportModal.blockersPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showQuickReportModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="submittingReport" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="submittingReport">{{ localeStore.t('detail.reportModal.submitting') }}</span>
              <span v-else>{{ localeStore.t('detail.reportModal.submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Upload Modal (with geolocation) -->
    <div
      v-if="showMediaUploadModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.mediaModal.title') }}</h3>
          <button @click="showMediaUploadModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitMediaUpload" class="space-y-3.5 mt-4 text-sm">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.mediaModal.selectReport') }}</label>
            <select v-model="mediaForm.reportId" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm">
              <option v-for="rep in dailyReports" :key="rep.id" :value="rep.id">
                {{ localeStore.t('detail.mediaModal.reportPrefix') }} {{ rep.report_date }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.mediaModal.file') }}</label>
            <input type="file" accept="image/*" capture="environment" @change="onFileSelected" required class="w-full text-xs text-slate-500 file:mr-0 file:me-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-900 hover:file:bg-amber-100" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.mediaModal.caption') }}</label>
            <input v-model="mediaForm.caption" :placeholder="localeStore.t('detail.mediaModal.captionPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div v-if="gpsCoords.lat" class="p-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs flex items-center gap-1.5 border border-emerald-200/60">
            <MapPin class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
            <span>{{ localeStore.t('detail.mediaModal.gpsCaptured') }} {{ gpsCoords.lat }}, {{ gpsCoords.lng }}</span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showMediaUploadModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="uploadingMedia" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="uploadingMedia">{{ localeStore.t('detail.mediaModal.uploading') }}</span>
              <span v-else>{{ localeStore.t('detail.mediaModal.submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Executive Summary Pulse Modal -->
    <div
      v-if="showPulseModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <Activity class="w-4 h-4" />
            </div>
            <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.pulse.title') }}</h3>
          </div>
          <button @click="showPulseModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div v-if="pulseData" class="mt-4 space-y-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-amber-50/70 border border-amber-200/70 p-3 rounded-xl">
              <span class="text-xs text-amber-800 font-medium block">{{ localeStore.t('detail.pulse.actualProgress') }}</span>
              <span class="text-2xl font-black text-amber-900">{{ pulseData.weighted_progress }}%</span>
            </div>
            <div class="bg-slate-50/80 border border-slate-200 p-3 rounded-xl">
              <span class="text-xs text-slate-500 font-medium block">{{ localeStore.t('detail.pulse.latestManpower') }}</span>
              <span class="text-2xl font-black text-slate-900">{{ pulseData.today_or_latest_manpower }} {{ localeStore.t('detail.pulse.workers') }}</span>
            </div>
            <div class="bg-slate-50/80 border border-slate-200 p-3 rounded-xl">
              <span class="text-xs text-slate-500 font-medium block">{{ localeStore.t('detail.pulse.totalClaimed') }}</span>
              <span class="text-base font-black text-slate-900">{{ formatCurrency(pulseData.total_claimed) }}</span>
            </div>
            <div class="bg-emerald-50/70 border border-emerald-200/70 p-3 rounded-xl">
              <span class="text-xs text-emerald-800 font-medium block">{{ localeStore.t('detail.pulse.approvedClaims') }}</span>
              <span class="text-base font-black text-emerald-900">{{ formatCurrency(pulseData.total_approved_claims) }}</span>
            </div>
          </div>

          <div v-if="pulseData.latest_report" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-xs">
            <span class="font-bold text-slate-800 block">
              {{ localeStore.t('detail.pulse.latestReport') }} ({{ pulseData.latest_report.report_date }}):
            </span>
            <p class="text-slate-600 whitespace-pre-line">{{ pulseData.latest_report.work_summary }}</p>
          </div>

          <div class="pt-3 border-t border-slate-100 text-center">
            <button @click="showPulseModal = false" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition text-xs sm:text-sm">
              {{ localeStore.t('common.close') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add BOQ Item Modal -->
    <div
      v-if="showAddBoqModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.boq.modalTitle') }}</h3>
          <button @click="showAddBoqModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitAddBoqItem" class="space-y-3.5 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.itemCode') }}</label>
              <input v-model="boqForm.item_code" required placeholder="BOQ-06" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.unit') }}</label>
              <select v-model="boqForm.unit" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm">
                <option value="م2">{{ localeStore.t('detail.boq.units.m2') }}</option>
                <option value="م3">{{ localeStore.t('detail.boq.units.m3') }}</option>
                <option value="متر طولي">{{ localeStore.t('detail.boq.units.lm') }}</option>
                <option value="طن">{{ localeStore.t('detail.boq.units.ton') }}</option>
                <option value="عدد">{{ localeStore.t('detail.boq.units.qty') }}</option>
                <option value="مقطوعية">{{ localeStore.t('detail.boq.units.lumpsum') }}</option>
                <option value="ساعة">{{ localeStore.t('detail.boq.units.hour') }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.descriptionOfWork') }}</label>
            <textarea v-model="boqForm.description" required rows="2" :placeholder="localeStore.t('detail.boq.descPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm"></textarea>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.totalQuantity') }}</label>
              <input v-model.number="boqForm.total_quantity" type="number" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.unitPriceLabel') }}</label>
              <input v-model.number="boqForm.unit_price" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.boq.weightPercentage') }}</label>
              <input v-model.number="boqForm.weight_percentage" type="number" step="0.01" min="0" max="100" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-xs">
            <span class="text-slate-600">{{ localeStore.t('detail.boq.totalEstimated') }}</span>
            <span class="font-black text-slate-900 text-sm">
              {{ formatCurrency((boqForm.total_quantity || 0) * (boqForm.unit_price || 0)) }}
            </span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showAddBoqModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="submittingBoq" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="submittingBoq">{{ localeStore.t('detail.boq.adding') }}</span>
              <span v-else>{{ localeStore.t('detail.boq.saveItem') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Upload Document Modal -->
    <div
      v-if="showUploadDocModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.drawings.uploadModalTitle') }}</h3>
          <button @click="showUploadDocModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitUploadDocument" class="space-y-3.5 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.drawings.docTitle') }}</label>
              <input v-model="docForm.title" required :placeholder="localeStore.isRtl ? 'مخطط تسليح الأعمدة' : 'Column Reinforcement Plan'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.drawings.docCode') }}</label>
              <input v-model="docForm.document_code" required placeholder="DWG-ST-202" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.drawings.disciplineLabel') }}</label>
              <select v-model="docForm.discipline" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm">
                <option value="architectural">{{ localeStore.t('disciplines.architectural') }}</option>
                <option value="structural">{{ localeStore.t('disciplines.structural') }}</option>
                <option value="mechanical">{{ localeStore.t('disciplines.mechanical') }}</option>
                <option value="electrical">{{ localeStore.t('disciplines.electrical') }}</option>
                <option value="contracts">{{ localeStore.t('disciplines.contracts') }}</option>
                <option value="permits">{{ localeStore.t('disciplines.permits') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.drawings.revisionLabel') }}</label>
              <input v-model="docForm.current_revision" placeholder="Rev 00" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200">
            <input
              id="ifcCheck"
              v-model="docForm.is_approved_for_construction"
              :disabled="authStore.userRole === 'site_engineer'"
              type="checkbox"
              class="w-4 h-4 mt-0.5 text-amber-500 rounded focus:ring-amber-500 disabled:opacity-40"
            />
            <label for="ifcCheck" class="text-xs font-bold text-slate-800 cursor-pointer">
              {{ localeStore.t('detail.drawings.ifcCheckbox') }}
              <span v-if="authStore.userRole === 'site_engineer'" class="text-[10px] text-amber-600 font-normal block mt-0.5">
                {{ localeStore.t('detail.drawings.ifcNotice') }}
              </span>
            </label>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.drawings.fileLabel') }}</label>
            <input type="file" @change="onDocFileSelected" required class="w-full text-xs text-slate-500 file:mr-0 file:me-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-900 hover:file:bg-amber-100" />
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showUploadDocModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="uploadingDoc" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="uploadingDoc">{{ localeStore.t('detail.drawings.uploading') }}</span>
              <span v-else>{{ localeStore.t('detail.drawings.submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Site Request Modal -->
    <div
      v-if="showCreateRequestModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.requests.createModalTitle') }}</h3>
          <button @click="showCreateRequestModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitCreateSiteRequest" class="space-y-3.5 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.typeLabel') }}</label>
              <select v-model="requestForm.type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm">
                <option value="WIR">{{ localeStore.t('requestTypes.WIR') }}</option>
                <option value="RFI">{{ localeStore.t('requestTypes.RFI') }}</option>
                <option value="VARIATION_ORDER">{{ localeStore.t('requestTypes.VARIATION_ORDER') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.numberLabel') }}</label>
              <input v-model="requestForm.request_number" :placeholder="localeStore.t('detail.requests.numberPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.reqTitle') }}</label>
            <input v-model="requestForm.title" required :placeholder="localeStore.isRtl ? 'مثال: طلب استلام حدادة أعمدة الدور الثاني' : 'e.g. 2nd Floor Column Reinforcement Inspection'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.descLabel') }}</label>
            <textarea v-model="requestForm.description" required rows="3" :placeholder="localeStore.t('detail.requests.descPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm"></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.locationLabel') }}</label>
            <input v-model="requestForm.location_details" :placeholder="localeStore.isRtl ? 'المحور B-4 إلى E-8، الطابق الأول' : 'Grid B-4 to E-8, First Floor'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div v-if="requestForm.type === 'VARIATION_ORDER'">
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.costImpactLabel') }}</label>
            <input v-model.number="requestForm.estimated_cost_impact" type="number" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showCreateRequestModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="submittingRequest" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="submittingRequest">{{ localeStore.t('detail.requests.submitting') }}</span>
              <span v-else>{{ localeStore.t('detail.requests.submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Payment Claim Modal -->
    <div
      v-if="showCreateClaimModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('detail.claims.createModalTitle') }}</h3>
          <button @click="showCreateClaimModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitCreateClaim" class="space-y-3.5 mt-4 text-sm">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.claims.claimNumber') }}</label>
            <input v-model="claimForm.claim_number" required placeholder="CLM-04" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.claims.periodStart') }}</label>
              <input v-model="claimForm.period_start" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.claims.periodEnd') }}</label>
              <input v-model="claimForm.period_end" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.claims.amountExVat') }}</label>
              <input v-model.number="claimForm.claimed_amount" @input="claimForm.vat_amount = Math.round(claimForm.claimed_amount * 0.15)" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.claims.vatAmount') }}</label>
              <input v-model.number="claimForm.vat_amount" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-xs">
            <span class="text-slate-600">{{ localeStore.t('detail.claims.totalClaimWithVat') }}</span>
            <span class="font-black text-slate-900 text-sm">
              {{ formatCurrency((claimForm.claimed_amount || 0) + (claimForm.vat_amount || 0)) }}
            </span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showCreateClaimModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="submittingClaim" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="submittingClaim">{{ localeStore.t('detail.claims.submitting') }}</span>
              <span v-else>{{ localeStore.t('detail.claims.submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Review Request Modal -->
    <div
      v-if="showReviewRequestModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">
            {{ reviewForm.status === 'approved' ? localeStore.t('detail.requests.reviewApproveTitle') : localeStore.t('detail.requests.reviewRejectTitle') }}
          </h3>
          <button @click="showReviewRequestModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitRequestReview" class="space-y-3.5 mt-4 text-sm">
          <div>
            <span class="text-xs text-slate-500 block mb-1">{{ localeStore.t('detail.requests.reviewTargetLabel') }}</span>
            <div class="font-bold text-slate-800 text-xs sm:text-sm">{{ reviewForm.title }}</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('detail.requests.reviewNotes') }}</label>
            <textarea v-model="reviewForm.response_notes" rows="3" :placeholder="localeStore.t('detail.requests.reviewNotesPlaceholder')" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showReviewRequestModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button
              type="submit"
              :disabled="submittingReview"
              class="px-5 py-2 text-white rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs"
              :class="reviewForm.status === 'approved' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'"
            >
              <span v-if="submittingReview">{{ localeStore.t('detail.requests.processing') }}</span>
              <span v-else>{{ reviewForm.status === 'approved' ? localeStore.t('detail.requests.confirmApprove') : localeStore.t('detail.requests.confirmReject') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  ArrowLeft,
  ArrowRight,
  Activity,
  Plus,
  X,
  Check,
  Download,
  Share2,
  Camera,
  MapPin,
  AlertCircle,
  ExternalLink,
  ClipboardList,
} from 'lucide-vue-next';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useLocaleStore } from '../stores/locale';

const route = useRoute();
const authStore = useAuthStore();
const localeStore = useLocaleStore();
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
const showReviewRequestModal = ref(false);

const pulseData = ref(null);
const submittingReport = ref(false);
const uploadingMedia = ref(false);
const submittingBoq = ref(false);
const uploadingDoc = ref(false);
const submittingRequest = ref(false);
const submittingClaim = ref(false);
const submittingReview = ref(false);

const selectedDiscipline = ref('all');
const gpsCoords = ref({ lat: null, lng: null });

const reportForm = ref({
  report_date: new Date().toISOString().split('T')[0],
  weather_condition: '34°C / Sunny',
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

const boqForm = ref({
  item_code: '',
  description: '',
  unit: 'م2',
  total_quantity: 100,
  unit_price: 150,
  weight_percentage: 10,
  current_progress_percentage: 0,
});

const docForm = ref({
  title: '',
  document_code: '',
  discipline: 'structural',
  current_revision: 'Rev 00',
  is_approved_for_construction: false,
  file: null,
});

const requestForm = ref({
  type: 'WIR',
  request_number: '',
  title: '',
  description: '',
  location_details: '',
  estimated_cost_impact: 0,
});

const claimForm = ref({
  claim_number: '',
  period_start: new Date().toISOString().split('T')[0],
  period_end: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  claimed_amount: 100000,
  vat_amount: 15000,
  status: 'submitted',
});

const reviewForm = ref({
  id: null,
  title: '',
  status: 'approved',
  response_notes: '',
});

const disciplines = computed(() => [
  { id: 'all', label: localeStore.t('disciplines.all') },
  { id: 'architectural', label: localeStore.t('disciplines.architectural') },
  { id: 'structural', label: localeStore.t('disciplines.structural') },
  { id: 'mechanical', label: localeStore.t('disciplines.mechanical') },
  { id: 'electrical', label: localeStore.t('disciplines.electrical') },
]);

const tabs = computed(() => [
  { id: 'reports', label: localeStore.t('detail.tabs.reports'), count: dailyReports.value.length },
  { id: 'boq', label: localeStore.t('detail.tabs.boq'), count: boqItems.value.length },
  { id: 'drawings', label: localeStore.t('detail.tabs.drawings'), count: projectDocuments.value.length },
  { id: 'requests', label: localeStore.t('detail.tabs.requests'), count: siteRequests.value.length },
  { id: 'claims', label: localeStore.t('detail.tabs.claims'), count: paymentClaims.value.length },
]);

const canManageBoq = computed(() => ['owner', 'pm'].includes(authStore.userRole));
const canApproveRequests = computed(() => ['owner', 'pm', 'viewer'].includes(authStore.userRole));
const canManageDocs = computed(() => ['owner', 'pm', 'site_engineer'].includes(authStore.userRole));
const canSubmitRequests = computed(() => ['owner', 'pm', 'site_engineer'].includes(authStore.userRole));

const allPhotos = computed(() => {
  const photos = [];
  dailyReports.value.forEach((r) => {
    if (r.media) {
      r.media.forEach((m) => photos.push(m));
    }
  });
  return photos;
});

const filteredDocuments = computed(() => {
  if (selectedDiscipline.value === 'all') return projectDocuments.value;
  return projectDocuments.value.filter((d) => d.discipline === selectedDiscipline.value);
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
    const item = boqItems.value.find((i) => i.id === itemId);
    if (item) {
      item.current_progress_percentage = parseFloat(newProgress);
    }
  } catch (err) {
    alert(localeStore.t('detail.boq.progressFailed'));
  }
};

const openQuickReportModal = () => {
  reportForm.value.weather_condition = localeStore.isRtl ? 'مشمس / 34°م' : 'Sunny / 34°C';
  showQuickReportModal.value = true;
};

const submitDailyReport = async () => {
  submittingReport.value = true;
  try {
    await api.post(`/projects/${projectId}/daily-reports`, reportForm.value);
    showQuickReportModal.value = false;
    await fetchProjectDetails();
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.reports.saveFailed'));
  } finally {
    submittingReport.value = false;
  }
};

const openMediaUploadModal = () => {
  if (dailyReports.value.length === 0) {
    alert(localeStore.t('detail.reports.needReportFirst'));
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
    alert(err.response?.data?.message || localeStore.t('detail.mediaModal.uploadFailed'));
  } finally {
    uploadingMedia.value = false;
  }
};

const shareOnWhatsApp = (report) => {
  const downloadUrl = report.export_url || `${window.location.origin}/api/v1/daily-reports/${report.id}/export-pdf?share_token=${report.share_token || ''}`;

  let text = '';
  if (localeStore.isRtl) {
    text = `*تقرير الموقع الميداني اليومي - ${project.value.name} (${project.value.code})*
📅 *التاريخ:* ${report.report_date}
👷 *العمالة الميدانية:* ${report.manpower_count} عامل
🌤 *الطقس:* ${report.weather_condition || 'معتدل'}
📊 *نسبة إنجاز المشروع:* ${project.value.weighted_progress}%

*ملخص الأعمال المنفذة:*
${report.work_summary}
${report.blockers_notes ? `\n*المعوقات والملاحظات:*\n${report.blockers_notes}` : ''}

🔗 يمكنك تحميل التقرير الرسمي المعتمد PDF من الرابط:
${downloadUrl}`;
  } else {
    text = `*Daily Field Site Report - ${project.value.name} (${project.value.code})*
📅 *Date:* ${report.report_date}
👷 *On-Site Manpower:* ${report.manpower_count} workers
🌤 *Weather:* ${report.weather_condition || 'Moderate'}
📊 *Project Weighted Progress:* ${project.value.weighted_progress}%

*Completed Work Summary:*
${report.work_summary}
${report.blockers_notes ? `\n*Site Blockers & Remarks:*\n${report.blockers_notes}` : ''}

🔗 Download the official PDF report here:
${downloadUrl}`;
  }

  const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
  window.open(url, '_blank');
};

const loadExecutiveSummary = async () => {
  try {
    const res = await api.get(`/projects/${projectId}/executive-summary`);
    pulseData.value = res.data.data;
    showPulseModal.value = true;
  } catch (err) {
    alert(localeStore.t('detail.pulse.failed'));
  }
};

const submitAddBoqItem = async () => {
  submittingBoq.value = true;
  try {
    const res = await api.post(`/projects/${projectId}/boq`, boqForm.value);
    boqItems.value.push(res.data.data.item);
    project.value.weighted_progress = res.data.data.project_weighted_progress;
    showAddBoqModal.value = false;
    boqForm.value = {
      item_code: '',
      description: '',
      unit: 'م2',
      total_quantity: 100,
      unit_price: 150,
      weight_percentage: 10,
      current_progress_percentage: 0,
    };
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.boq.addFailed'));
  } finally {
    submittingBoq.value = false;
  }
};

const onDocFileSelected = (e) => {
  docForm.value.file = e.target.files[0];
};

const submitUploadDocument = async () => {
  if (!docForm.value.file) {
    alert(localeStore.t('detail.drawings.selectFile'));
    return;
  }
  uploadingDoc.value = true;
  const fd = new FormData();
  fd.append('title', docForm.value.title);
  fd.append('document_code', docForm.value.document_code);
  fd.append('discipline', docForm.value.discipline);
  fd.append('current_revision', docForm.value.current_revision);
  fd.append('is_approved_for_construction', docForm.value.is_approved_for_construction ? '1' : '0');
  fd.append('file', docForm.value.file);

  try {
    const res = await api.post(`/projects/${projectId}/documents`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    projectDocuments.value.unshift(res.data.data);
    showUploadDocModal.value = false;
    docForm.value = {
      title: '',
      document_code: '',
      discipline: 'structural',
      current_revision: 'Rev 00',
      is_approved_for_construction: false,
      file: null,
    };
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.drawings.uploadFailed'));
  } finally {
    uploadingDoc.value = false;
  }
};

const submitCreateSiteRequest = async () => {
  submittingRequest.value = true;
  try {
    const res = await api.post(`/projects/${projectId}/requests`, requestForm.value);
    siteRequests.value.unshift(res.data.data);
    showCreateRequestModal.value = false;
    requestForm.value = {
      type: 'WIR',
      request_number: '',
      title: '',
      description: '',
      location_details: '',
      estimated_cost_impact: 0,
    };
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.requests.submitFailed'));
  } finally {
    submittingRequest.value = false;
  }
};

const openReviewModal = (req, targetStatus) => {
  reviewForm.value = {
    id: req.id,
    title: req.title,
    status: targetStatus,
    response_notes: '',
  };
  showReviewRequestModal.value = true;
};

const submitRequestReview = async () => {
  submittingReview.value = true;
  try {
    const res = await api.patch(`/requests/${reviewForm.value.id}/status`, {
      status: reviewForm.value.status,
      response_notes: reviewForm.value.response_notes,
    });
    const idx = siteRequests.value.findIndex((r) => r.id === reviewForm.value.id);
    if (idx !== -1) {
      siteRequests.value[idx] = res.data.data;
    }
    showReviewRequestModal.value = false;
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.requests.updateFailed'));
  } finally {
    submittingReview.value = false;
  }
};

const submitCreateClaim = async () => {
  submittingClaim.value = true;
  try {
    const res = await api.post(`/projects/${projectId}/claims`, claimForm.value);
    paymentClaims.value.unshift(res.data.data);
    showCreateClaimModal.value = false;
    claimForm.value = {
      claim_number: '',
      period_start: new Date().toISOString().split('T')[0],
      period_end: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      claimed_amount: 100000,
      vat_amount: 15000,
      status: 'submitted',
    };
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.claims.submitFailed'));
  } finally {
    submittingClaim.value = false;
  }
};

const approveDailyReport = async (reportId) => {
  if (!confirm(localeStore.t('detail.reports.confirmApprove'))) return;
  try {
    await api.patch(`/daily-reports/${reportId}/status`, { status: 'approved' });
    const idx = dailyReports.value.findIndex((r) => r.id === reportId);
    if (idx !== -1) {
      dailyReports.value[idx].status = 'approved';
    }
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.reports.approveFailed'));
  }
};

const updateClaimStatusAction = async (claimId, newStatus) => {
  try {
    await api.patch(`/claims/${claimId}/status`, { status: newStatus });
    const idx = paymentClaims.value.findIndex((c) => c.id === claimId);
    if (idx !== -1) {
      paymentClaims.value[idx].status = newStatus;
    }
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('detail.claims.updateFailed'));
  }
};

const translateBoqUnit = (unit) => {
  const map = {
    'م2': localeStore.t('detail.boq.units.m2'),
    'م3': localeStore.t('detail.boq.units.m3'),
    'متر طولي': localeStore.t('detail.boq.units.lm'),
    'طن': localeStore.t('detail.boq.units.ton'),
    'عدد': localeStore.t('detail.boq.units.qty'),
    'مقطوعية': localeStore.t('detail.boq.units.lumpsum'),
    'ساعة': localeStore.t('detail.boq.units.hour'),
  };
  return map[unit] || unit;
};

const getRequestTypeBadge = (t) => {
  return t === 'VARIATION_ORDER' ? 'bg-purple-50 text-purple-700 border border-purple-200/70' : (t === 'WIR' ? 'bg-sky-50 text-sky-700 border border-sky-200/70' : 'bg-amber-50 text-amber-700 border border-amber-200/70');
};

const getRequestStatusBadge = (s) => {
  return s === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : (s === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200/70' : 'bg-amber-50 text-amber-700 border border-amber-200/70');
};

const getClaimStatusBadge = (s) => {
  return s === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : (s === 'certified' ? 'bg-sky-50 text-sky-700 border border-sky-200/70' : 'bg-amber-50 text-amber-700 border border-amber-200/70');
};

const formatCurrency = (val) => {
  return localeStore.formatCurrency(val);
};

onMounted(fetchProjectDetails);
</script>
