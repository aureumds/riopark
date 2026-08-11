import Dexie from 'dexie';

export const db = new Dexie('rio_park_operator');

db.version(1).stores({
  meta: 'key',
  shifts: 'local_uuid, sync_status',
  sessions: 'local_uuid, plate_normalized, status, sync_status',
  sync_queue: '++id, type, created_at',
});

export async function getMeta(key) {
  const row = await db.meta.get(key);
  return row?.value;
}

export async function setMeta(key, value) {
  await db.meta.put({ key, value });
}
