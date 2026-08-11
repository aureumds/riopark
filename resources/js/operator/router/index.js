import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import LoginView from '../views/LoginView.vue';
import HomeView from '../views/HomeView.vue';
import EntryView from '../views/EntryView.vue';
import ExitView from '../views/ExitView.vue';
import YardView from '../views/YardView.vue';
import ShiftView from '../views/ShiftView.vue';

const routes = [
  { path: '/operador/login', name: 'login', component: LoginView, meta: { guest: true } },
  { path: '/operador', name: 'home', component: HomeView },
  { path: '/operador/entrada', name: 'entry', component: EntryView },
  { path: '/operador/saida', name: 'exit', component: ExitView },
  { path: '/operador/patio', name: 'yard', component: YardView },
  { path: '/operador/turno', name: 'shift', component: ShiftView },
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
    return { name: 'home' };
  }
});

export default router;
