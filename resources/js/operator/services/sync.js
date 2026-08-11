import api from '../api/client';
import { db } from '../db';

export async function processSyncQueue() {
  const pending = await db.sync_queue.orderBy('created_at').toArray();
  if (!pending.length) return { ok: true };

  const events = pending.map((item) => item.payload);

  try {
    const { data } = await api.post('/sync/push', { events });
    const results = data.results || [];

    for (const result of results) {
      if (result.status === 'synced') {
        const queueItem = pending.find((p) => p.payload.local_uuid === result.local_uuid);
        if (queueItem) await db.sync_queue.delete(queueItem.id);
      }
    }

    return { ok: true, results };
  } catch {
    return { ok: false };
  }
}

export function queueEvent(type, payload) {
  return db.sync_queue.add({
    type,
    payload: { type, ...payload },
    created_at: Date.now(),
  });
}

export async function pendingCount() {
  return await db.sync_queue.count();
}
