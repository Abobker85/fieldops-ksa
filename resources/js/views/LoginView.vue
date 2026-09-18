<template>
  <div
    class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 px-4 relative overflow-hidden transition-colors duration-200"
    :dir="localeStore.isRtl ? 'rtl' : 'ltr'"
  >
    <!-- Background subtle gradient glow -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 start-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-amber-500/10 blur-[120px] rounded-full"></div>
    </div>

    <!-- Top Language Switcher Bar -->
    <div class="absolute top-6 end-6 z-10">
      <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs shadow-sm">
        <button
          @click="localeStore.setLocale('ar')"
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          :class="localeStore.locale === 'ar' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
        >
          العربية
        </button>
        <button
          @click="localeStore.setLocale('en')"
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          :class="localeStore.locale === 'en' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
        >
          English
        </button>
      </div>
    </div>

    <!-- Header / Brand -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center relative z-10">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 font-black text-xl shadow-md shadow-amber-500/20 mb-4">
        FO
      </div>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
        {{ localeStore.t('login.title') }}
      </h2>
      <p class="mt-2 text-xs sm:text-sm text-slate-400 max-w-sm mx-auto">
        {{ localeStore.t('login.subtitle') }}
      </p>
    </div>

    <!-- Main Card -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="bg-slate-900/90 backdrop-blur-md border border-slate-800/90 py-8 px-6 shadow-xl rounded-2xl sm:px-10">
        <form class="space-y-4" @submit.prevent="handleLogin">
          <!-- Error Alert -->
          <div
            v-if="errorMsg"
            class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs flex items-center gap-2.5"
          >
            <AlertCircle class="w-4 h-4 text-rose-400 shrink-0" />
            <span>{{ errorMsg }}</span>
          </div>

          <!-- Email Input -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 text-start">
              {{ localeStore.t('login.email') }}
            </label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/60 focus:border-amber-500 text-sm transition"
              :placeholder="localeStore.t('login.emailPlaceholder')"
            />
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 text-start">
              {{ localeStore.t('login.password') }}
            </label>
            <input
              v-model="password"
              type="password"
              required
              class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/60 focus:border-amber-500 text-sm transition"
              :placeholder="localeStore.t('login.passwordPlaceholder')"
            />
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full mt-2 py-3 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md shadow-amber-500/15 active:scale-[0.99] transition disabled:opacity-50 text-sm flex items-center justify-center gap-2"
          >
            <span v-if="loading">{{ localeStore.t('login.submitting') }}</span>
            <span v-else>{{ localeStore.t('login.submit') }}</span>
          </button>
        </form>

        <!-- Quick Demo Switcher -->
        <div class="mt-8 pt-6 border-t border-slate-800">
          <p class="text-[11px] font-semibold text-slate-400 mb-3 text-center">
            {{ localeStore.t('login.quickAccess') }}
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              @click="quickLogin('owner@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 rounded-xl text-start transition group"
            >
              <div class="font-bold text-amber-400 group-hover:text-amber-300 text-xs">
                {{ localeStore.t('login.ownerTitle') }}
              </div>
              <div class="text-slate-400 text-[10px] mt-0.5">
                {{ localeStore.t('login.ownerSubtitle') }}
              </div>
            </button>

            <button
              @click="quickLogin('pm@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 rounded-xl text-start transition group"
            >
              <div class="font-bold text-sky-400 group-hover:text-sky-300 text-xs">
                {{ localeStore.t('login.pmTitle') }}
              </div>
              <div class="text-slate-400 text-[10px] mt-0.5">
                {{ localeStore.t('login.pmSubtitle') }}
              </div>
            </button>

            <button
              @click="quickLogin('engineer@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 rounded-xl text-start transition group"
            >
              <div class="font-bold text-emerald-400 group-hover:text-emerald-300 text-xs">
                {{ localeStore.t('login.engineerTitle') }}
              </div>
              <div class="text-slate-400 text-[10px] mt-0.5">
                {{ localeStore.t('login.engineerSubtitle') }}
              </div>
            </button>

            <button
              @click="quickLogin('consultant@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-950/60 hover:bg-slate-800/80 border border-slate-800/80 rounded-xl text-start transition group"
            >
              <div class="font-bold text-purple-400 group-hover:text-purple-300 text-xs">
                {{ localeStore.t('login.consultantTitle') }}
              </div>
              <div class="text-slate-400 text-[10px] mt-0.5">
                {{ localeStore.t('login.consultantSubtitle') }}
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { AlertCircle } from 'lucide-vue-next';
import { useAuthStore } from '../stores/auth';
import { useLocaleStore } from '../stores/locale';

const router = useRouter();
const authStore = useAuthStore();
const localeStore = useLocaleStore();

const email = ref('engineer@fieldops.sa');
const password = ref('password123');
const loading = ref(false);
const errorMsg = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMsg.value = '';
  try {
    await authStore.login(email.value, password.value);
    router.push('/');
  } catch (err) {
    errorMsg.value = err.response?.data?.message || (typeof err === 'string' ? err : localeStore.t('login.failed'));
  } finally {
    loading.value = false;
  }
};

const quickLogin = (userEmail) => {
  email.value = userEmail;
  password.value = 'password123';
  handleLogin();
};
</script>
