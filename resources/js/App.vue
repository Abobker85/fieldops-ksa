<template>
  <div
    class="min-h-screen min-h-[100dvh] w-full flex flex-col antialiased transition-colors duration-200"
    :class="isAuthPage ? 'bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-800'"
    :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
  >
    <!-- Top Navigation Bar (Only for Authenticated Users) -->
    <header v-if="authStore.isAuthenticated && !isAuthPage" class="bg-slate-900 text-white sticky top-0 z-40 shadow-sm border-b border-slate-800 w-full">
      <div class="w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Brand & Tenant Name -->
        <div class="flex items-center gap-3">
          <router-link to="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-sm shadow-sm group-hover:bg-amber-400 transition">
              FO
            </div>
            <div>
              <span class="font-extrabold text-base tracking-tight text-white block leading-none">
                {{ localeStore.t('common.appName') }}
              </span>
              <span class="text-[11px] text-amber-400/90 font-medium block mt-1 truncate max-w-[180px] sm:max-w-none">
                {{ authStore.tenant?.name || localeStore.t('common.tenantFallback') }}
              </span>
            </div>
          </router-link>
        </div>

        <!-- Right Side: Language Switcher, User Profile, Logout -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Segmented Language Switcher -->
          <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-800/80 border border-slate-700/70 text-xs">
            <button
              @click="localeStore.setLocale('ar')"
              type="button"
              class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
              :class="localeStore.locale === 'ar' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
            >
              العربية
            </button>
            <button
              @click="localeStore.setLocale('en')"
              type="button"
              class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
              :class="localeStore.locale === 'en' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
            >
              English
            </button>
          </div>

          <!-- User Details (Name & Email/Tenant) -->
          <div class="hidden md:flex flex-col text-start text-xs">
            <span class="font-bold text-slate-100 leading-tight">{{ authStore.userName }}</span>
            <span class="text-[11px] text-slate-400 truncate max-w-[150px]">{{ authStore.user?.email || authStore.tenant?.name }}</span>
          </div>

          <!-- Role Badge -->
          <span
            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border"
            :class="getRoleBadgeClass(authStore.userRole)"
          >
            {{ localeStore.translateRole(authStore.userRole) }}
          </span>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            :title="localeStore.t('common.logout')"
            class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <!-- Main View Container: Full Bleed for Login / Auth, Fluid Spacing for Dashboard -->
    <main
      class="flex-1 w-full"
      :class="isAuthPage ? 'p-0 m-0 max-w-none' : 'w-full px-4 sm:px-6 lg:px-8 py-6'"
    >
      <router-view />
    </main>

    <!-- Clean Minimalist Footer -->
    <footer v-if="authStore.isAuthenticated && !isAuthPage" class="bg-white border-t border-slate-200/80 py-3.5 text-center text-xs text-slate-500 w-full">
      <div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="text-slate-500">{{ localeStore.t('common.footer') }}</p>
        <span class="text-[11px] font-mono text-slate-400">v1.2.0 • FieldOps KSA</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { LogOut } from 'lucide-vue-next';
import { useAuthStore } from './stores/auth';
import { useLocaleStore } from './stores/locale';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const localeStore = useLocaleStore();

const isAuthPage = computed(() => route.name === 'Login' || !authStore.isAuthenticated);

watch(isAuthPage, (val) => {
  if (typeof document !== 'undefined') {
    if (val) {
      if (document.body) {
        document.body.classList.remove('bg-slate-50', 'text-slate-800');
        document.body.classList.add('bg-slate-950', 'text-slate-100');
      }
      document.documentElement.classList.add('bg-slate-950');
    } else {
      if (document.body) {
        document.body.classList.remove('bg-slate-950', 'text-slate-100');
        document.body.classList.add('bg-slate-50', 'text-slate-800');
      }
      document.documentElement.classList.remove('bg-slate-950');
    }
  }
}, { immediate: true });

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'owner':
      return 'bg-amber-500/10 text-amber-300 border-amber-500/30';
    case 'pm':
      return 'bg-sky-500/10 text-sky-300 border-sky-500/30';
    case 'site_engineer':
      return 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30';
    default:
      return 'bg-purple-500/10 text-purple-300 border-purple-500/30';
  }
};

const updateDocTitle = () => {
  if (typeof document !== 'undefined') {
    document.title = localeStore.isRtl
      ? 'FieldOps KSA | إدارة العمليات الميدانية للمقاولات'
      : 'FieldOps KSA | Construction Field Operations Platform';
  }
};

watch(() => localeStore.locale, updateDocTitle, { immediate: true });

onMounted(() => {
  if (authStore.isAuthenticated) {
    authStore.fetchProfile();
  }
});
</script>
