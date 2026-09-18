import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('fieldops_token') || null,
        user: JSON.parse(localStorage.getItem('fieldops_user') || 'null'),
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        tenant: (state) => state.user?.tenant || null,
        userRole: (state) => state.user?.roles?.[0] || 'viewer',
        userName: (state) => state.user?.name || '',
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const res = await api.post('/login', { email, password });
                const { token, user } = res.data.data;

                this.token = token;
                this.user = user;

                localStorage.setItem('fieldops_token', token);
                localStorage.setItem('fieldops_user', JSON.stringify(user));

                return user;
            } catch (err) {
                this.error = err.response?.data?.message || null;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/logout');
                }
            } catch (e) {
                // Ignore errors on logout
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('fieldops_token');
                localStorage.removeItem('fieldops_user');
            }
        },

        async fetchProfile() {
            if (!this.token) return null;
            try {
                const res = await api.get('/profile');
                this.user = res.data.data;
                localStorage.setItem('fieldops_user', JSON.stringify(this.user));
                return this.user;
            } catch (e) {
                this.logout();
                return null;
            }
        },
    },
});
