<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-tajawal" dir="rtl">
    <!-- Top Navigation Bar (Only for Authenticated Users) -->
    <header v-if="authStore.isAuthenticated" class="bg-slate-900 text-white sticky top-0 z-40 shadow-md border-b border-slate-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Brand & Tenant Name -->
        <div class="flex items-center gap-3">
          <router-link to="/" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-sm shadow-md shadow-amber-500/20">
              FO
            </div>
            <div>
              <span class="font-extrabold text-base tracking-tight text-white block leading-none">FieldOps KSA</span>
              <span class="text-[10px] text-amber-400 font-semibold block mt-0.5">{{ authStore.tenant?.name || 'منصة المقاولات' }}</span>
            </div>
          </router-link>
        </div>

        <!-- User Profile & Logout -->
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex flex-col text-left text-xs">
            <span class="font-bold text-slate-200">{{ authStore.userName }}</span>
            <span class="text-[10px] text-slate-400 text-right">{{ translateRole(authStore.userRole) }}</span>
          </div>

          <span class="px-2.5 py-1 rounded-full text-[11px] font-bold" :class="getRoleBadgeClass(authStore.userRole)">
            {{ translateRole(authStore.userRole) }}
          </span>

          <button
            @click="handleLogout"
            title="تسجيل الخروج"
            class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-xl transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Main View Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
      <router-view />
    </main>

    <!-- Mobile-First Bottom Status or Footer -->
    <footer v-if="authStore.isAuthenticated" class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500">
      <p>منصة FieldOps KSA لإدارة العمليات الميدانية • التوثيق الفوري وحساب الإنجاز الموزون للمقاولات</p>
    </footer>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const translateRole = (role) => {
  const map = {
    owner: 'المدير العام',
    pm: 'مدير المشاريع',
    site_engineer: 'مهندس الموقع',
    viewer: 'استشاري / مالك',
  };
  return map[role] || role;
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'owner':
      return 'bg-amber-500/20 text-amber-300 border border-amber-500/40';
    case 'pm':
      return 'bg-sky-500/20 text-sky-300 border border-sky-500/40';
    case 'site_engineer':
      return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
    default:
      return 'bg-purple-500/20 text-purple-300 border border-purple-500/40';
  }
};

onMounted(() => {
  if (authStore.isAuthenticated) {
    authStore.fetchProfile();
  }
});
</script>
