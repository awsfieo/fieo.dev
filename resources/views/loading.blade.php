<!doctype html>
<html>
<head><meta charset="utf-8"><title>Preparing PDF…</title></head>
<body>
<div id="msg">Preparing PDF…</div>

<script>
(function () {
    const statusUrl = @json($statusUrl);

    async function poll() {
        const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' }});
        const data = await res.json();

        if (data.status === 'ready' && data.url) {
            window.location.href = data.url; // download starts (file already generated)
            return;
        }

        setTimeout(poll, 800);
    }

    poll();
})();
</script>
</body>
</html>
