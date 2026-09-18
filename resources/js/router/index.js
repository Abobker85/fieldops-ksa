import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import ProjectsView from '../views/ProjectsView.vue';
import ProjectDetailView from '../views/ProjectDetailView.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: LoginView,
        meta: { guestOnly: true },
    },
    {
        path: '/',
        name: 'Projects',
        component: ProjectsView,
        meta: { requiresAuth: true },
    },
    {
        path: '/projects/:id',
        name: 'ProjectDetail',
        component: ProjectDetailView,
        meta: { requiresAuth: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('fieldops_token');

    if (to.meta.requiresAuth && !token) {
        next({ name: 'Login' });
    } else if (to.meta.guestOnly && token) {
        next({ name: 'Projects' });
    } else {
        next();
    }
});

export default router;
