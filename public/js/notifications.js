// public/js/notifications.js
(() => {
  console.log('notifications.js loaded');
  const badge = document.getElementById('notif-count');
  if (!badge) return;

  async function updateBadge() {
    try {
      const res = await fetch('/notifications/unread');
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const notifications = await res.json();
      const count = Array.isArray(notifications) ? notifications.length : 0;

      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    } catch (err) {
      console.error('Błąd pobierania powiadomień:', err);
      badge.style.display = 'none';
    }
  }

  // pierwsze wywołanie + co 30 sekund
  updateBadge();
  setInterval(updateBadge, 30000);
})();
