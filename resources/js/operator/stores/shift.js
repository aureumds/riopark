import { defineStore } from 'pinia';
import { uuidv4 } from '../../uuid';
import api from '../api/client';
import { db, getMeta, setMeta } from '../db';
import { queueEvent } from '../services/sync';
import { useAuthStore } from './auth';

export const useShiftStore = defineStore('shift', {
  state: () => ({
    current: null,
  }),

  actions: {
    async init() {
      const local = await getMeta('currentShift');
      if (local) this.current = local;

      const auth = useAuthStore();
      if (auth.online && auth.token) {
        try {
          const { data } = await api.get('/shifts/current');
          if (data.shift) {
            this.current = data.shift;
            await setMeta('currentShift', data.shift);
          }
        } catch {
          /* use local */
        }
      }
    },

    async open(openingBalance = 0) {
      const auth = useAuthStore();
      const local_uuid = uuidv4();
      const shift = {
        local_uuid,
        opened_at: new Date().toISOString(),
        opening_balance: openingBalance,
        closed_at: null,
      };

      if (auth.online) {
        try {
          const { data } = await api.post('/shifts/open', {
            local_uuid,
            opening_balance: openingBalance,
          });
          this.current = data.shift;
          await setMeta('currentShift', data.shift);
          return data.shift;
        } catch {
          /* fallback offline */
        }
      }

      this.current = shift;
      await setMeta('currentShift', shift);
      await db.shifts.put({ ...shift, sync_status: 'pending' });
      await queueEvent('shift_open', {
        local_uuid,
        opened_at: shift.opened_at,
        opening_balance: openingBalance,
        parking_lot_id: auth.parkingLot?.id,
      });
      auth.pendingSync = await db.sync_queue.count();
      return shift;
    },

    async close(closingBalance = 0) {
      const auth = useAuthStore();
      if (!this.current) return;

      const payload = {
        local_uuid: this.current.local_uuid,
        closed_at: new Date().toISOString(),
        closing_balance: closingBalance,
      };

      if (auth.online) {
        try {
          const { data } = await api.post('/shifts/close', payload);
          this.current = null;
          await setMeta('currentShift', null);
          return data.shift;
        } catch {
          /* fallback */
        }
      }

      await db.shifts.update(this.current.local_uuid, {
        closed_at: payload.closed_at,
        closing_balance: closingBalance,
        sync_status: 'pending',
      });
      await queueEvent('shift_close', payload);
      this.current = null;
      await setMeta('currentShift', null);
      auth.pendingSync = await db.sync_queue.count();
    },
  },
});
