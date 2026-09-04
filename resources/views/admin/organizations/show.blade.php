@extends('layouts.admin')

@section('title', $organization->name)

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.organizations.index') }}" class="text-sm text-secondary font-semibold hover:underline">&larr; Kembali ke daftar organisasi</a>
        <h1 class="text-2xl font-extrabold text-primary mt-2">{{ $organization->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $organization->slug }} &middot; {{ $organization->organizationType?->name ?? 'Tanpa jenis' }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg bg-secondary/10 border border-secondary/20 text-secondary px-4 py-3 text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
        <h2 class="font-bold text-gray-800 mb-4">Paket Saat Ini</h2>
        <div class="flex flex-wrap items-center gap-x-8 gap-y-2 text-sm">
            <div>
                <p class="text-xs text-gray-400">Paket</p>
                <p class="font-semibold text-gray-800">{{ $organization->plan?->name ?? 'Belum memilih paket' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Aktif Hingga</p>
                <p class="font-semibold text-gray-800">
                    {{ $organization->plan_expires_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                @if ($organization->hasPaidForCurrentPlan())
                    <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Aktif</span>
                @else
                    <span class="px-2 py-1 rounded-full bg-red-50 text-red-500 text-xs font-semibold">Belum/Tidak Aktif</span>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
        <h2 class="font-bold text-gray-800">Ubah Paket Manual</h2>
        <p class="text-xs text-gray-400 mt-0.5 mb-4">
            Mengubah paket &amp; masa aktif organisasi secara langsung, tanpa melalui pengajuan pembayaran.
            Gunakan untuk kasus khusus (komplain, kesepakatan di luar sistem, perbaikan data) &mdash; setiap
            perubahan tercatat di riwayat di bawah.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.organizations.override-plan', $organization) }}" method="POST"
              x-data @submit.prevent="if (await confirmAction('Ubah paket organisasi ini secara manual?', { danger: false })) $el.submit()">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="plan_id" class="block text-sm font-semibold text-gray-700 mb-1">Paket</label>
                    <select name="plan_id" id="plan_id" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Pilih paket&hellip;</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id', $organization->plan_id) == $plan->id)>
                                {{ $plan->name }} &mdash; {{ $plan->formattedPrice() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="plan_expires_at" class="block text-sm font-semibold text-gray-700 mb-1">Aktif Hingga</label>
                    <input type="datetime-local" name="plan_expires_at" id="plan_expires_at" required
                           value="{{ old('plan_expires_at', $organization->plan_expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
            </div>
            <div class="mb-4">
                <label for="note" class="block text-sm font-semibold text-gray-700 mb-1">Alasan</label>
                <textarea name="note" id="note" rows="2" required placeholder="Wajib diisi, mis. &quot;Komplain pembayaran, dikonfirmasi via WhatsApp 02 Sep 2026&quot;"
                          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors cursor-pointer">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6">
        <h2 class="font-bold text-gray-800 mb-4">Riwayat Perubahan Paket</h2>

        @if ($organization->planOverrideLogs->isEmpty())
            <p class="text-sm text-gray-400">Belum ada perubahan paket manual untuk organisasi ini.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($organization->planOverrideLogs as $log)
                    <div class="py-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-gray-800">{{ $log->action->label() }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            oleh {{ $log->admin->name }}
                            &mdash; {{ $log->fromPlan?->name ?? '—' }} ({{ $log->from_expires_at?->translatedFormat('d M Y') ?? '—' }})
                            &rarr; {{ $log->toPlan?->name ?? '—' }} ({{ $log->to_expires_at?->translatedFormat('d M Y') ?? '—' }})
                        </p>
                        @if ($log->note)
                            <p class="text-xs text-gray-400 mt-1 italic">&ldquo;{{ $log->note }}&rdquo;</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6 mt-6 border border-red-100">
        <h2 class="font-bold text-red-600 mb-1">Hapus Organisasi</h2>
        <p class="text-sm text-gray-500 mb-4">
            Tindakan ini akan menghapus organisasi beserta seluruh data anggotanya secara permanen dan tidak dapat
            dibatalkan.
        </p>
        <form action="{{ route('admin.organizations.destroy', $organization) }}" method="POST"
            onsubmit="return confirm('Hapus organisasi ' + {{ Illuminate\Support\Js::from($organization->name) }} + '? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-4 py-2 rounded-full bg-red-500 text-white text-sm font-semibold hover:bg-red-600 cursor-pointer">
                Hapus Organisasi
            </button>
        </form>
    </div>
@endsection
