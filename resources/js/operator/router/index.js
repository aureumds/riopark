import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import LoginView from '../views/LoginView.vue';
import HomeView from '../views/HomeView.vue';
import EntryView from '../views/EntryView.vue';
import ExitView from '../views/ExitView.vue';
import YardView from '../views/YardView.vue';
import ShiftView from '../views/ShiftView.vue';
import ClosingView from '../views/ClosingView.vue';
import LicenseView from '../views/LicenseView.vue';

const routes = [
  { path: '/operador/login', name: 'login', component: LoginView, meta: { guest: true } },
  { path: '/operador/licenca', name: 'license', component: LicenseView },
  { path: '/operador', name: 'home', component: HomeView, meta: { needsLicense: true } },
  { path: '/operador/entrada', name: 'entry', component: EntryView, meta: { needsLicense: true } },
  { path: '/operador/saida', name: 'exit', component: ExitView, meta: { needsLicense: true } },
  { path: '/operador/patio', name: 'yard', component: YardView, meta: { needsLicense: true } },
  { path: '/operador/turno', name: 'shift', component: ShiftView, meta: { needsLicense: true } },
  { path: '/operador/fechamento', name: 'closing', component: ClosingView, meta: { needsLicense: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const auth = useAuthStore();
  if (!to.meta.guest && !auth.token) {
    return { name: 'login' };
  }
  if (to.meta.guest && auth.token) {
    return auth.licenseValid ? { name: 'home' } : { name: 'license' };
  }
  if (to.meta.needsLicense && auth.token && !auth.licenseValid) {
    return { name: 'license' };
  }
});

export default router;
