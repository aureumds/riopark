import { defineStore } from 'pinia';
import api, { setAuthToken } from '../api/client';
import { db, getMeta, setMeta } from '../db';
import { processSyncQueue } from '../services/sync';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
    company: null,
    parkingLot: null,
    tariff: null,
    settings: null,
    online: navigator.onLine,
    pendingSync: 0,
  }),

  actions: {
    async init() {
      window.addEventListener('online', () => {
        this.online = true;
        this.sync();
      });
      window.addEventListener('offline', () => {
        this.online = false;
      });

      const token = await getMeta('token');
      const user = await getMeta('user');

      if (token && user) {
        this.token = token;
        this.user = user;
        setAuthToken(token);
        await this.loadBootstrap();
      }

      this.pendingSync = await db.sync_queue.count();
    },

    async login(email, password) {
      const { data } = await api.post('/auth/login', { email, password });
      this.token = data.token;
      this.user = data.user;
      setAuthToken(data.token);
      await setMeta('token', data.token);
      await setMeta('user', data.user);
      await this.loadBootstrap();
      await this.sync();
    },

    async loadBootstrap() {
      if (!this.online || !this.token) {
        this.tariff = await getMeta('tariff');
        this.settings = await getMeta('settings');
        this.company = await getMeta('company');
        this.parkingLot = await getMeta('parkingLot');
        return;
      }

      try {
        const { data } = await api.get('/bootstrap');
        this.user = data.user;
        this.company = data.company;
        this.parkingLot = data.parking_lot;
        this.tariff = data.tariff;
        this.settings = data.settings;
        await setMeta('user', data.user);
        await setMeta('company', data.company);
        await setMeta('parkingLot', data.parking_lot);
        await setMeta('tariff', data.tariff);
        await setMeta('settings', data.settings);
      } catch {
        this.tariff = await getMeta('tariff');
        this.settings = await getMeta('settings');
      }
    },

    async logout() {
      try {
        if (this.online) await api.post('/auth/logout');
      } catch {
        /* ignore */
      }
      this.token = null;
      this.user = null;
      setAuthToken(null);
      await db.meta.clear();
    },

    async sync() {
      if (!this.online || !this.token) return;
      await processSyncQueue();
      this.pendingSync = await db.sync_queue.count();
      await this.loadBootstrap();
    },
  },
});
