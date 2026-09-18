<template>
  <div class="space-y-6">
    <!-- Executive Top Pulse -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
      <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">{{ localeStore.t('projects.activeProjects') }}</span>
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ localeStore.formatNumber(projects.length) }}</span>
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold">
            {{ localeStore.t('projects.inProgress') }}
          </span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">{{ localeStore.t('projects.avgProgress') }}</span>
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight">{{ averageProgress }}%</span>
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60 font-semibold">
            {{ localeStore.t('projects.onSite') }}
          </span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">{{ localeStore.t('projects.totalContractValue') }}</span>
          <span class="w-2 h-2 rounded-full bg-sky-500"></span>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <span class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">{{ formatCurrency(totalContractValue) }}</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">{{ localeStore.t('projects.dailyReportsCount') }}</span>
          <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
        </div>
        <div class="mt-3 flex items-baseline justify-between">
          <span class="text-2xl sm:text-3xl font-black text-indigo-600 tracking-tight">{{ localeStore.formatNumber(totalReportsCount) }}</span>
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/60 font-semibold">
            {{ localeStore.t('projects.reportsUnit') }}
          </span>
        </div>
      </div>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-xs">
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="localeStore.t('projects.searchPlaceholder')"
          class="w-full ps-10 pe-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:bg-white transition"
        />
        <Search class="w-4 h-4 text-slate-400 absolute start-3.5 top-3 pointer-events-none" />
      </div>

      <button
        v-if="canCreateProject"
        @click="showCreateModal = true"
        class="flex items-center justify-center gap-2 py-2 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-xs active:scale-98 transition text-sm whitespace-nowrap"
      >
        <Plus class="w-4 h-4" />
        <span>{{ localeStore.t('projects.newProject') }}</span>
      </button>
    </div>

    <!-- Projects Grid -->
    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-3 border-amber-500 border-t-transparent"></div>
      <p class="mt-2 text-xs text-slate-500">{{ localeStore.t('projects.loading') }}</p>
    </div>

    <div v-else-if="filteredProjects.length === 0" class="text-center py-16 bg-white rounded-2xl border border-slate-200 p-8 shadow-xs">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
        <Building2 class="w-7 h-7 stroke-[1.5]" />
      </div>
      <h3 class="text-base font-bold text-slate-800">{{ localeStore.t('projects.emptyTitle') }}</h3>
      <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ localeStore.t('projects.emptyDesc') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="project in filteredProjects"
        :key="project.id"
        @click="$router.push(`/projects/${project.id}`)"
        class="bg-white rounded-2xl p-5 border border-slate-200/80 hover:border-amber-500/60 hover:shadow-md transition duration-200 cursor-pointer flex flex-col justify-between group"
      >
        <div>
          <!-- Header & Badges -->
          <div class="flex items-start justify-between gap-3">
            <div>
              <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-mono font-bold rounded-md mb-1.5 border border-slate-200/60">
                {{ project.code }}
              </span>
              <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-amber-600 transition">
                {{ project.name }}
              </h3>
            </div>
            <span
              class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap flex items-center gap-1.5"
              :class="project.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-slate-100 text-slate-600 border border-slate-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="project.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
              {{ project.status === 'active' ? localeStore.t('statuses.active') : localeStore.translateStatus(project.status) }}
            </span>
          </div>

          <!-- Metadata -->
          <div class="mt-4 grid grid-cols-2 gap-2.5 text-xs text-slate-600">
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">{{ localeStore.t('projects.client') }}</span>
              <span class="font-semibold text-slate-800 truncate block">{{ project.client_name }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">{{ localeStore.t('projects.city') }}</span>
              <span class="font-semibold text-slate-800">{{ project.location_city }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">{{ localeStore.t('projects.contractValue') }}</span>
              <span class="font-bold text-slate-900">{{ formatCurrency(project.contract_value) }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">{{ localeStore.t('projects.consultant') }}</span>
              <span class="font-semibold text-slate-800 truncate block">{{ project.consultant_name || localeStore.t('projects.notSpecified') }}</span>
            </div>
          </div>

          <!-- Progress Engine Bar -->
          <div class="mt-5">
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="font-semibold text-slate-700">{{ localeStore.t('projects.weightedProgressBoq') }}</span>
              <span class="font-black text-amber-600 text-sm">{{ project.weighted_progress || 0 }}%</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
              <div
                class="bg-gradient-to-r from-amber-500 to-amber-400 h-full rounded-full transition-all duration-500"
                :style="{ width: `${Math.min(100, project.weighted_progress || 0)}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Card Footer Summary -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <div class="flex items-center gap-2 sm:gap-3 text-xs text-slate-500">
            <span class="font-medium text-slate-600">
              <strong class="text-slate-800 font-bold">{{ project.daily_reports_count || 0 }}</strong> {{ localeStore.t('projects.dailyReports') }}
            </span>
            <span class="text-slate-300">•</span>
            <span class="font-medium text-slate-600">
              <strong class="text-slate-800 font-bold">{{ project.documents_count || 0 }}</strong> {{ localeStore.t('projects.drawings') }}
            </span>
            <span class="text-slate-300">•</span>
            <span class="font-medium text-slate-600">
              <strong class="text-slate-800 font-bold">{{ project.site_requests_count || 0 }}</strong> {{ localeStore.t('projects.requests') }}
            </span>
          </div>

          <span class="text-amber-600 font-bold group-hover:text-amber-700 transition flex items-center gap-1 text-xs">
            <span>{{ localeStore.t('projects.viewOperations') }}</span>
            <component :is="localeStore.isRtl ? ArrowLeft : ArrowRight" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
          </span>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
      :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">{{ localeStore.t('projects.modal.title') }}</h3>
          <button @click="showCreateModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="createProject" class="space-y-3.5 mt-4 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.code') }}</label>
              <input v-model="newProject.code" required placeholder="PRJ-2026-03" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.name') }}</label>
              <input v-model="newProject.name" required :placeholder="localeStore.isRtl ? 'مشروع مجمع الأعمال' : 'Business Center Project'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.clientName') }}</label>
              <input v-model="newProject.client_name" required :placeholder="localeStore.isRtl ? 'شركة التطوير العقاري' : 'Real Estate Development Co.'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.consultantName') }}</label>
              <input v-model="newProject.consultant_name" :placeholder="localeStore.isRtl ? 'دار الاستشارات الهندسية' : 'Engineering Consultants'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.city') }}</label>
              <input v-model="newProject.location_city" required :placeholder="localeStore.isRtl ? 'الرياض' : 'Riyadh'" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.contractValue') }}</label>
              <input v-model.number="newProject.contract_value" type="number" required min="0" placeholder="5000000" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.startDate') }}</label>
              <input v-model="newProject.start_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">{{ localeStore.t('projects.modal.expectedEndDate') }}</label>
              <input v-model="newProject.expected_end_date" type="date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 focus:outline-none text-xs sm:text-sm" />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl font-semibold text-xs sm:text-sm transition">
              {{ localeStore.t('common.cancel') }}
            </button>
            <button type="submit" :disabled="creating" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl font-bold transition disabled:opacity-50 text-xs sm:text-sm shadow-xs">
              <span v-if="creating">{{ localeStore.t('projects.modal.saving') }}</span>
              <span v-else>{{ localeStore.t('projects.modal.saveBtn') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  Building2,
  Search,
  Plus,
  X,
  ArrowLeft,
  ArrowRight,
} from 'lucide-vue-next';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useLocaleStore } from '../stores/locale';

const authStore = useAuthStore();
const localeStore = useLocaleStore();

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
  location_city: localeStore.isRtl ? 'الرياض' : 'Riyadh',
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
      location_city: localeStore.isRtl ? 'الرياض' : 'Riyadh',
      contract_value: 1000000,
      start_date: new Date().toISOString().split('T')[0],
      expected_end_date: new Date(Date.now() + 180 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      status: 'active',
    };
    await fetchProjects();
  } catch (err) {
    alert(err.response?.data?.message || localeStore.t('projects.createFailed'));
  } finally {
    creating.value = false;
  }
};

const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  const q = searchQuery.value.toLowerCase();
  return projects.value.filter((p) =>
    (p.name && p.name.toLowerCase().includes(q)) ||
    (p.code && p.code.toLowerCase().includes(q)) ||
    (p.location_city && p.location_city.toLowerCase().includes(q))
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
  return localeStore.formatCurrency(val);
};

onMounted(fetchProjects);
</script>
