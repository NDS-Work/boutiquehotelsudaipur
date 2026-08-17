/**
 * tracker-snippet.js
 * Drop this into your header.php <head> (after GTM).
 * No cookies. No personal data. EU GDPR safe.
 * Hashed IP handled server-side. This script only sends page metadata.
 */
(function () {
  // Don't track admin pages
  if (window.location.pathname.startsWith('/admin')) return;

  var payload = {
    page_url:   window.location.href,
    page_title: document.title,
    referrer:   document.referrer || ''
  };

  // Use sendBeacon if available (fires even on page unload)
  var endpoint = '/track.php';
  var body = JSON.stringify(payload);

  if (navigator.sendBeacon) {
    navigator.sendBeacon(endpoint, new Blob([body], { type: 'application/json' }));
  } else {
    fetch(endpoint, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    body,
      keepalive: true
    }).catch(function(){});
  }
})();
