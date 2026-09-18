<template>
  <div class="space-y-6">
    <!-- Executive Top Pulse -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <span class="text-xs font-medium text-slate-500">المشاريع النشطة</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ projects.length }}</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold">قيد العمل</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <span class="text-xs font-medium text-slate-500">متوسط الإنجاز الموزون</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-amber-600">{{ averageProgress }}%</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold">ميداني</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <span class="text-xs font-medium text-slate-500">إجمالي قيمة العقود</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-lg sm:text-xl font-black text-slate-900">{{ formatCurrency(totalContractValue) }}</span>
          <span class="text-[11px] text-slate-400">ر.س</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <span class="text-xs font-medium text-slate-500">التقارير اليومية المسجلة</span>
        <div class="mt-2 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-sky-600">{{ totalReportsCount }}</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 font-bold">تقرير</span>
        </div>
      </div>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="بحث باسم المشروع أو الكود أو المدينة..."
          class="w-full pr-10 pl-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>

      <button
        v-if="canCreateProject"
        @click="showCreateModal = true"
        class="flex items-center justify-center gap-2 py-2.5 px-5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-sm active:scale-95 transition text-sm whitespace-nowrap"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        إضافة مشروع جديد
      </button>
    </div>

    <!-- Projects Grid -->
    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-amber-500 border-t-transparent"></div>
      <p class="mt-2 text-sm text-slate-500">جاري تحميل بيانات المشاريع الميدانية...</p>
    </div>

    <div v-else-if="filteredProjects.length === 0" class="text-center py-16 bg-white rounded-2xl border border-slate-200 p-8">
      <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
      </div>
      <h3 class="text-base font-bold text-slate-800">لم يتم العثور على مشاريع</h3>
      <p class="text-xs text-slate-500 mt-1">ابدأ بإنشاء مشروع جديد لإدارة العمليات الميدانية واليوميات.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="project in filteredProjects"
        :key="project.id"
        @click="$router.push(`/projects/${project.id}`)"
        class="bg-white rounded-2xl p-5 border border-slate-200/90 hover:border-amber-500/80 hover:shadow-md transition cursor-pointer flex flex-col justify-between group"
      >
        <div>
          <!-- Header & Badges -->
          <div class="flex items-start justify-between gap-3">
            <div>
              <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-mono font-bold rounded-lg mb-1.5">
                {{ project.code }}
              </span>
              <h3 class="text-lg font-bold text-slate-900 group-hover:text-amber-600 transition">
                {{ project.name }}
              </h3>
            </div>
            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap"
              :class="project.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'"
            >
              {{ project.status === 'active' ? 'نشط ميدانياً' : project.status }}
            </span>
          </div>

          <!-- Metadata -->
          <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600">
            <div>
              <span class="text-slate-400 block text-[11px]">المالك:</span>
              <span class="font-medium text-slate-800">{{ project.client_name }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">المدينة:</span>
              <span class="font-medium text-slate-800">{{ project.location_city }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">قيمة العقد:</span>
              <span class="font-bold text-slate-900">{{ formatCurrency(project.contract_value) }} ر.س</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">الاستشاري:</span>
              <span class="font-medium text-slate-800 truncate block">{{ project.consultant_name || 'غير محدد' }}</span>
            </div>
          </div>

          <!-- Progress Engine Bar -->
          <div class="mt-5">
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="font-semibold text-slate-700">نسبة الإنجاز الموزونة (BOQ)</span>
              <span class="font-black text-amber-600 text-sm">{{ project.weighted_progress || 0 }}%</span>
            </div>
            <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
              <div
                class="bg-gradient-to-r from-amber-500 to-amber-400 h-full rounded-full transition-all duration-500"
                :style="{ width: `${Math.min(100, project.weighted_progress || 0)}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Card Footer Summary -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <div class="flex items-center gap-4">
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              {{ project.daily_reports_count || 0 }} يوميات
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
              {{ project.documents_count || 0 }} مخططات
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
              {{ project.site_requests_count || 0 }} طلبات
            </span>
          </div>
          <span class="text-amber-600 font-bold group-hover:translate-x-[-2px] transition flex items-center gap-0.5">
            عرض العمليات ←
          </span>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" dir="rtl">
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">إنشاء مشروع ميداني جديد</h3>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="createProject" class="space-y-4 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-slate-700 mb-1">كود المشروع</label>
              <input v-model="newProject.code" required placeholder="PRJ-2026-03" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-medium text-slate-700 mb-1">اسم المشروع</label>
              <input v-model="newProject.name" required placeholder="مشروع مجمع الأعمال" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-slate-700 mb-1">اسم المالك / العميل</label>
              <input v-model="newProject.client_name" required placeholder="شركة التطوير العقاري" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-medium text-slate-700 mb-1">المكتب الاستشاري</label>
              <input v-model="newProject.consultant_name" placeholder="دار الاستشارات" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-slate-700 mb-1">المدينة</label>
              <input v-model="newProject.location_city" required placeholder="الرياض" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-medium text-slate-700 mb-1">قيمة العقد (SAR)</label>
              <input v-model.number="newProject.contract_value" type="number" required min="0" placeholder="5000000" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-medium text-slate-700 mb-1">تاريخ البدء</label>
              <input v-model="newProject.start_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-medium text-slate-700 mb-1">تاريخ الانتهاء المتوقع</label>
              <input v-model="newProject.expected_end_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-medium">إلغاء</button>
            <button type="submit" :disabled="creating" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50">
              <span v-if="creating">جاري الحفظ...</span>
              <span v-else>حفظ وتفعيل المشروع</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const projects = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const showCreateModal = ref(false);
const creating = ref(false);

const newProject = ref({
  code: '',
  name: '',
  client_name: '',
  consultant_name: '',
  location_city: 'الرياض',
  contract_value: 1000000,
  start_date: new Date().toISOString().split('T')[0],
  expected_end_date: new Date(Date.now() + 180 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  status: 'active',
});

const canCreateProject = computed(() => {
  return ['owner', 'pm'].includes(authStore.userRole);
});

const fetchProjects = async () => {
  loading.value = true;
  try {
    const res = await api.get('/projects');
    projects.value = res.data.data;
  } catch (err) {
    console.error('Failed to load projects:', err);
  } finally {
    loading.value = false;
  }
};

const createProject = async () => {
  creating.value = true;
  try {
    await api.post('/projects', newProject.value);
    showCreateModal.value = false;
    newProject.value = {
      code: '',
      name: '',
      client_name: '',
      consultant_name: '',
      location_city: 'الرياض',
      contract_value: 1000000,
      start_date: new Date().toISOString().split('T')[0],
      expected_end_date: new Date(Date.now() + 180 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      status: 'active',
    };
    await fetchProjects();
  } catch (err) {
    alert(err.response?.data?.message || 'فشل إنشاء المشروع');
  } finally {
    creating.value = false;
  }
};

const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  const q = searchQuery.value.toLowerCase();
  return projects.value.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.code.toLowerCase().includes(q) ||
    p.location_city.toLowerCase().includes(q)
  );
});

const totalContractValue = computed(() => {
  return projects.value.reduce((acc, p) => acc + (parseFloat(p.contract_value) || 0), 0);
});

const averageProgress = computed(() => {
  if (!projects.value.length) return 0;
  const total = projects.value.reduce((acc, p) => acc + (parseFloat(p.weighted_progress) || 0), 0);
  return Math.round(total / projects.value.length);
});

const totalReportsCount = computed(() => {
  return projects.value.reduce((acc, p) => acc + (parseInt(p.daily_reports_count) || 0), 0);
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('ar-SA', { maximumFractionDigits: 0 }).format(val || 0);
};

onMounted(fetchProjects);
</script>
