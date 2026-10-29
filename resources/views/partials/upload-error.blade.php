{{--
    uploadErrorMessage(res): turns a failed upload fetch() Response into one sentence a tenant
    can act on. Every upload point (media pickers, builder, article cover) used to handle this
    its own way - most spread the 422 body into the gallery as if it were a list of uploads, so
    a rejected file just vanished with no reason given ("kok tidak masuk?"), and the article
    cover showed a generic "coba lagi" that hid an actionable "simpan ulang sebagai JPG".

    Kept as an inline global next to confirmAction() rather than in resources/js: these views
    already call that helper the same way, and deploys ship committed public/build assets, so
    a Blade partial changes without a Vite rebuild.
--}}
<script>
    async function uploadErrorMessage(res) {
        // 413 comes from nginx/PHP before Laravel runs, so its body is HTML, not JSON.
        if (res.status === 413) return 'Ukuran file terlalu besar. Maks 10MB.';
        if (res.status === 419) return 'Sesi Anda sudah berakhir. Muat ulang halaman lalu coba lagi.';
        if (res.status === 429) return 'Terlalu banyak unggahan dalam waktu singkat. Tunggu sebentar lalu coba lagi.';

        let body = null;
        try {
            body = await res.json();
        } catch (e) {}

        // Laravel 422 shape: {message, errors: {"files.0": [msg], "files.1": [msg]}} - one
        // line per rejected file, so a multi-file upload says which ones failed.
        const errors = [...new Set(Object.values(body?.errors ?? {}).flat())];
        if (errors.length) return errors.join('\n');

        // A 500's message is Laravel's English "Server Error", not something to show as-is.
        if (res.status < 500 && body?.message) return body.message;

        return `Gagal mengunggah gambar (kode ${res.status}). Coba lagi, atau hubungi admin jika terus terjadi.`;
    }
</script>
