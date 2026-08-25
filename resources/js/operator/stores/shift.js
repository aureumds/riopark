import { defineStore } from 'pinia';
import { uuidv4 } from '../../uuid';
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
      this.current = local || null;
    },

    async open(openingBalance = 0) {
      const auth = useAuthStore();
      if (!auth.licenseValid) {
        throw new Error('Licença vencida. Conecte à internet e renove.');
      }

      const local_uuid = uuidv4();
      const shift = {
        local_uuid,
        opened_at: new Date().toISOString(),
        opening_balance: openingBalance,
        closed_at: null,
        parking_lot_id: auth.parkingLot?.id,
      };

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
      if (auth.online) {
        auth.sync();
      }
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

      await db.shifts.update(this.current.local_uuid, {
        closed_at: payload.closed_at,
        closing_balance: closingBalance,
        sync_status: 'pending',
      });
      await queueEvent('shift_close', payload);
      this.current = null;
      await setMeta('currentShift', null);
      auth.pendingSync = await db.sync_queue.count();
      if (auth.online) {
        auth.sync();
      }
    },
  },
});
