export async function sha256Hex(text) {
  const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(text));
  return [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, '0')).join('');
}

export async function hashPassword(password, salt) {
  return sha256Hex(`${salt}:${password}`);
}

export function licenseStatus(license, lastKnownUtc, now = Date.now()) {
  if (!license?.expires_at) {
    return { valid: false, reason: 'missing', daysLeft: 0, grace: false };
  }

  if (lastKnownUtc && now + 60_000 < lastKnownUtc) {
    return { valid: false, reason: 'clock', daysLeft: 0, grace: false };
  }

  const expires = new Date(license.expires_at).getTime();
  const graceDays = Number(license.grace_days ?? 3);
  const graceUntil = expires + graceDays * 24 * 60 * 60 * 1000;
  const daysLeft = Math.ceil((expires - now) / (24 * 60 * 60 * 1000));

  if (now <= expires) {
    return { valid: true, reason: null, daysLeft, grace: false };
  }

  if (now <= graceUntil) {
    return { valid: true, reason: null, daysLeft: 0, grace: true };
  }

  return { valid: false, reason: 'expired', daysLeft: 0, grace: false };
}
