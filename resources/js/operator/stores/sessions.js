import { defineStore } from 'pinia';
import { uuidv4 } from '../../uuid';
import api from '../api/client';
import { db } from '../db';
import { queueEvent } from '../services/sync';
import { calculateAmount, formatPlate, normalizePlate } from '../services/tariff';
import { useAuthStore } from './auth';
import { useShiftStore } from './shift';

export const useSessionStore = defineStore('sessions', {
  state: () => ({
    active: [],
  }),

  actions: {
    async loadActive() {
      const auth = useAuthStore();
      const localActive = await db.sessions.where('status').equals('active').toArray();

      if (auth.online && auth.token) {
        try {
          const { data } = await api.get('/sessions/active');
          this.active = data.sessions;
          for (const s of data.sessions) {
            await db.sessions.put({ ...s, local_uuid: s.local_uuid, sync_status: 'synced' });
          }
          return;
        } catch {
          /* fallback */
        }
      }

      this.active = localActive;
    },

    async registerEntry(plate) {
      const auth = useAuthStore();
      const shiftStore = useShiftStore();
      if (!shiftStore.current) {
        await shiftStore.open(0);
      }

      const normalized = normalizePlate(plate);
      const exists = this.active.some((s) => s.plate_normalized === normalized);
      if (exists) throw new Error('Placa já está no pátio');

      const local_uuid = uuidv4();
      const session = {
        local_uuid,
        plate: formatPlate(plate),
        plate_normalized: normalized,
        entry_at: new Date().toISOString(),
        status: 'active',
        shift_local_uuid: shiftStore.current?.local_uuid,
      };

      if (auth.online) {
        try {
          const { data } = await api.post('/sessions/entry', {
            plate,
            local_uuid,
          });
          this.active.push(data.session);
          await db.sessions.put({ ...data.session, sync_status: 'synced' });
          this.maybePrint('entry', data.session);
          return data.session;
        } catch (e) {
          if (e.response?.status === 422) throw new Error(e.response.data.message);
        }
      }

      await db.sessions.put({ ...session, sync_status: 'pending' });
      await queueEvent('session_entry', {
        local_uuid,
        plate,
        entry_at: session.entry_at,
        parking_lot_id: auth.parkingLot?.id,
        shift_local_uuid: shiftStore.current?.local_uuid,
      });
      this.active.push(session);
      auth.pendingSync = await db.sync_queue.count();
      this.maybePrint('entry', session);
      return session;
    },

    async registerExit(plate) {
      const auth = useAuthStore();
      const normalized = normalizePlate(plate);
      const session = this.active.find((s) => s.plate_normalized === normalized);

      if (!session) throw new Error('Veículo não encontrado');

      const exitAt = new Date();
      const amount = calculateAmount(auth.tariff, session.entry_at, exitAt);

      if (auth.online) {
        try {
          const { data } = await api.post('/sessions/exit', { plate });
          await db.sessions.update(session.local_uuid, {
            status: 'completed',
            exit_at: data.session.exit_at,
            amount: data.amount,
            sync_status: 'synced',
          });
          this.active = this.active.filter((s) => s.local_uuid !== session.local_uuid);
          this.maybePrint('exit', { ...session, amount: data.amount, exit_at: exitAt.toISOString() });
          return { amount: data.amount, session: data.session };
        } catch (e) {
          if (e.response?.status === 404) throw new Error('Veículo não encontrado');
        }
      }

      await db.sessions.update(session.local_uuid, {
        status: 'completed',
        exit_at: exitAt.toISOString(),
        amount,
        sync_status: 'pending',
      });
      await queueEvent('session_exit', {
        local_uuid: session.local_uuid,
        exit_at: exitAt.toISOString(),
        amount,
        payment_method: 'cash',
      });
      this.active = this.active.filter((s) => s.local_uuid !== session.local_uuid);
      auth.pendingSync = await db.sync_queue.count();
      this.maybePrint('exit', { ...session, amount, exit_at: exitAt.toISOString() });
      return { amount, session };
    },

    async preview(plate) {
      const auth = useAuthStore();
      const normalized = normalizePlate(plate);
      const session = this.active.find((s) => s.plate_normalized === normalized);
      if (!session) throw new Error('Veículo não encontrado');

      if (auth.online) {
        try {
          const { data } = await api.post('/sessions/preview', { plate });
          return data;
        } catch {
          /* fallback */
        }
      }

      const amount = calculateAmount(auth.tariff, session.entry_at);
      const duration_minutes = Math.floor((Date.now() - new Date(session.entry_at)) / 60000);
      return { session, amount, duration_minutes };
    },

    maybePrint(type, session) {
      const auth = useAuthStore();
      const shouldPrint =
        type === 'entry' ? auth.settings?.print_ticket_on_entry : auth.settings?.print_ticket_on_exit;

      if (!shouldPrint) return;

      const text = this.buildTicketText(type, session, auth);
      if (window.RioParkBridge?.printTicket) {
        window.RioParkBridge.printTicket(text);
      }
    },

    buildTicketText(type, session, auth) {
      const lines = [
        auth.company?.name || 'Rio Park',
        auth.parkingLot?.name || '',
        type === 'entry' ? 'ENTRADA' : 'SAIDA',
        `Placa: ${session.plate}`,
        `Entrada: ${new Date(session.entry_at).toLocaleString('pt-BR')}`,
      ];
      if (type === 'exit') {
        lines.push(`Saida: ${new Date(session.exit_at).toLocaleString('pt-BR')}`);
        lines.push(`Total: R$ ${Number(session.amount).toFixed(2)}`);
      }
      return lines.join('\n');
    },
  },
});
