export function calculateAmount(tariff, entryAt, exitAt = new Date()) {
  if (!tariff) return 0;

  const entry = new Date(entryAt);
  const exit = new Date(exitAt);
  const minutes = Math.floor((exit - entry) / 60000);

  if (minutes <= tariff.grace_minutes) return 0;

  const billable = minutes - tariff.grace_minutes;
  const fractionMinutes = Math.max(1, tariff.fraction_minutes || 30);

  if (tariff.fraction_price > 0) {
    const fractions = Math.ceil(billable / fractionMinutes);
    return Math.round(fractions * parseFloat(tariff.fraction_price) * 100) / 100;
  }

  const hours = Math.ceil(billable / 60);
  return Math.round(hours * parseFloat(tariff.price_per_hour) * 100) / 100;
}

export function normalizePlate(plate) {
  return (plate || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
}

export function formatPlate(plate) {
  const n = normalizePlate(plate);
  if (n.length === 7) return `${n.slice(0, 3)}-${n.slice(3)}`;
  return n;
}
