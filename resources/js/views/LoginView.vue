<template>
  <div class="min-h-screen bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 px-4" dir="rtl">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <!-- Logo & Branding -->
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl shadow-lg shadow-amber-500/30 mb-4">
        FO
      </div>
      <h2 class="text-3xl font-extrabold text-white tracking-tight">
        FieldOps KSA
      </h2>
      <p class="mt-2 text-sm text-slate-400">
        منصة إدارة العمليات الميدانية وتوثيق المشاريع للمقاولات
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-slate-800/90 backdrop-blur border border-slate-700 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
        <form class="space-y-5" @submit.prevent="handleLogin">
          <div v-if="errorMsg" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-2">
            <span class="font-bold">خطأ:</span> {{ errorMsg }}
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">البريد الإلكتروني</label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full px-4 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition"
              placeholder="name@company.sa"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">كلمة المرور</label>
            <input
              v-model="password"
              type="password"
              required
              class="w-full px-4 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/20 active:scale-[0.98] transition disabled:opacity-50 text-base"
          >
            <span v-if="loading">جاري التحقق...</span>
            <span v-else>تسجيل الدخول إلى الميدان</span>
          </button>
        </form>

        <!-- Quick Demo Switcher -->
        <div class="mt-8 pt-6 border-t border-slate-700/80">
          <p class="text-xs font-semibold text-slate-400 mb-3 text-center">
            أو اختر حساباً تجريبياً للدخول الفوري:
          </p>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              @click="quickLogin('owner@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-900/90 hover:bg-slate-700/80 border border-slate-700 rounded-xl text-right transition"
            >
              <div class="font-bold text-amber-400">المدير العام</div>
              <div class="text-slate-400 text-[10px] mt-0.5">Owner / GM</div>
            </button>

            <button
              @click="quickLogin('pm@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-900/90 hover:bg-slate-700/80 border border-slate-700 rounded-xl text-right transition"
            >
              <div class="font-bold text-sky-400">مدير المشاريع</div>
              <div class="text-slate-400 text-[10px] mt-0.5">Project Manager</div>
            </button>

            <button
              @click="quickLogin('engineer@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-900/90 hover:bg-slate-700/80 border border-slate-700 rounded-xl text-right transition"
            >
              <div class="font-bold text-emerald-400">مهندس الموقع</div>
              <div class="text-slate-400 text-[10px] mt-0.5">Site Engineer</div>
            </button>

            <button
              @click="quickLogin('consultant@fieldops.sa')"
              type="button"
              class="p-2.5 bg-slate-900/90 hover:bg-slate-700/80 border border-slate-700 rounded-xl text-right transition"
            >
              <div class="font-bold text-purple-400">استشاري المالك</div>
              <div class="text-slate-400 text-[10px] mt-0.5">Consultant</div>
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
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

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
    errorMsg.value = err;
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
