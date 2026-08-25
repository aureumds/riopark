import { defineStore } from 'pinia';
import { uuidv4 } from '../../uuid';
import api, { setAuthToken } from '../api/client';
import { db, getMeta, setMeta } from '../db';
import { hashPassword, licenseStatus } from '../services/license';
import { processSyncQueue } from '../services/sync';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
    company: null,
    parkingLot: null,
    tariff: null,
    settings: null,
    license: null,
    deviceUid: null,
    lastKnownUtc: null,
    online: typeof navigator !== 'undefined' ? navigator.onLine : true,
    pendingSync: 0,
  }),

  getters: {
    licenseCheck(state) {
      return licenseStatus(state.license, state.lastKnownUtc);
    },
    licenseValid() {
      return this.licenseCheck.valid;
    },
    daysLeft() {
      return this.licenseCheck.daysLeft;
    },
  },

  actions: {
    async init() {
      window.addEventListener('online', () => {
        this.online = true;
        this.sync();
      });
      window.addEventListener('offline', () => {
        this.online = false;
      });

      let deviceUid = await getMeta('device_uid');
      if (!deviceUid) {
        deviceUid = uuidv4();
        await setMeta('device_uid', deviceUid);
      }
      this.deviceUid = deviceUid;

      this.token = await getMeta('token');
      this.user = await getMeta('user');
      this.license = await getMeta('license');
      this.lastKnownUtc = await getMeta('last_known_utc');
      this.tariff = await getMeta('tariff');
      this.settings = await getMeta('settings');
      this.company = await getMeta('company');
      this.parkingLot = await getMeta('parkingLot');

      if (this.token && this.token !== 'local') {
        setAuthToken(this.token);
      }

      await this.touchClock();

      if (this.token && this.online && this.token !== 'local') {
        await this.loadBootstrap();
      }

      this.pendingSync = await db.sync_queue.count();
    },

    async touchClock() {
      const now = Date.now();
      if (!this.lastKnownUtc || now >= this.lastKnownUtc) {
        this.lastKnownUtc = now;
        await setMeta('last_known_utc', now);
      }
    },

    async persistLicense(license) {
      if (!license) return;
      this.license = license;
      await setMeta('license', license);
    },

    async persistSession(data, password) {
      this.token = data.token;
      this.user = data.user;
      setAuthToken(data.token);
      await setMeta('token', data.token);
      await setMeta('user', data.user);

      if (data.license) {
        await this.persistLicense(data.license);
      }

      if (password) {
        const salt = (await getMeta('cred_salt')) || uuidv4();
        await setMeta('cred_salt', salt);
        await setMeta('cred_email', data.user.email.toLowerCase());
        await setMeta('cred_hash', await hashPassword(password, salt));
      }
    },

    async login(email, password) {
      if (this.online) {
        try {
          const { data } = await api.post('/license/activate', {
            email,
            password,
            device_uid: this.deviceUid || (await getMeta('device_uid')),
          });
          await this.persistSession(data, password);
          await this.loadBootstrap();
          await this.sync();
          return;
        } catch (e) {
          const status = e.response?.status;
          if (status === 422) {
            const errors = e.response?.data?.errors;
            const first = errors ? Object.values(errors)[0] : null;
            throw new Error((Array.isArray(first) ? first[0] : first) || e.response?.data?.message || 'Falha no login');
          }
          if (this.user && (await this.loginLocal(email, password))) {
            return;
          }
          throw e;
        }
      }

      const ok = await this.loginLocal(email, password);
      if (!ok) {
        throw new Error('Sem internet. Use o mesmo e-mail e senha do último acesso nesta máquina.');
      }
    },

    async loginLocal(email, password) {
      const savedEmail = await getMeta('cred_email');
      const salt = await getMeta('cred_salt');
      const hash = await getMeta('cred_hash');
      if (!savedEmail || !salt || !hash) return false;
      if (savedEmail !== email.trim().toLowerCase()) return false;
      const next = await hashPassword(password, salt);
      if (next !== hash) return false;

      this.user = await getMeta('user');
      this.token = (await getMeta('token')) || 'local';
      this.license = await getMeta('license');
      this.tariff = await getMeta('tariff');
      this.settings = await getMeta('settings');
      this.company = await getMeta('company');
      this.parkingLot = await getMeta('parkingLot');
      if (this.token && this.token !== 'local') {
        setAuthToken(this.token);
      }
      return true;
    },

    async renewLicenseWithPassword(password) {
      if (!this.online) {
        throw new Error('Conecte à internet para renovar a licença.');
      }
      const email = this.user?.email || (await getMeta('cred_email'));
      const { data } = await api.post('/license/renew', {
        email,
        password,
        device_uid: this.deviceUid,
      });
      await this.persistSession(data, password);
      await this.loadBootstrap();
    },

    async loadBootstrap() {
      if (!this.online || !this.token || this.token === 'local') {
        this.tariff = await getMeta('tariff');
        this.settings = await getMeta('settings');
        this.company = await getMeta('company');
        this.parkingLot = await getMeta('parkingLot');
        this.license = (await getMeta('license')) || this.license;
        return;
      }

      try {
        const { data } = await api.get('/bootstrap', { params: { device_uid: this.deviceUid } });
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
        if (data.license) {
          await this.persistLicense(data.license);
        }
      } catch (e) {
        if (e.response?.status === 403) {
          this.license = { ...this.license, expires_at: new Date(0).toISOString() };
          await setMeta('license', this.license);
        }
        this.tariff = await getMeta('tariff');
        this.settings = await getMeta('settings');
      }
    },

    async logout() {
      try {
        if (this.online && this.token && this.token !== 'local') {
          await api.post('/auth/logout');
        }
      } catch {
        /* ignore */
      }
      this.token = null;
      this.user = null;
      setAuthToken(null);
      await setMeta('token', null);
    },

    async sync() {
      if (!this.online || !this.token || this.token === 'local') return;
      if (!this.licenseValid) return;
      await processSyncQueue();
      this.pendingSync = await db.sync_queue.count();
      await this.loadBootstrap();
    },
  },
});
