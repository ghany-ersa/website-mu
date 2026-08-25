@extends('layouts.admin')

@section('title', 'Organisasi')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-primary">Organisasi Terdaftar</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $total }} organisasi terdaftar.</p>
        </div>
        <div class="flex items-center gap-2 bg-white rounded-full p-1 shadow-soft text-sm font-medium">
            <a href="{{ route('admin.organizations.index', ['group_by' => 'owner']) }}"
               class="px-4 py-2 rounded-full {{ $groupBy === 'owner' ? 'bg-primary text-white' : 'text-gray-500' }}">
                Kelompokkan per Owner
            </a>
            <a href="{{ route('admin.organizations.index', ['group_by' => 'type']) }}"
               class="px-4 py-2 rounded-full {{ $groupBy === 'type' ? 'bg-primary text-white' : 'text-gray-500' }}">
                Kelompokkan per Jenis
            </a>
        </div>
    </div>

    @forelse ($groups as $groupName => $organizations)
        <div class="mb-8">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">
                {{ $groupName }}
                <span class="font-medium normal-case text-gray-400">({{ $organizations->count() }})</span>
            </h2>

            <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Jenis Organisasi</th>
                            <th class="px-5 py-3">Owner</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($organizations as $organization)
                            @php $owner = $organization->members->first(); @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-gray-800">{{ $organization->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $organization->slug }}</p>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $organization->organizationType?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    @if ($owner)
                                        <p>{{ $owner->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $owner->email }}</p>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($organization->status === \App\Enums\OrganizationStatus::Published)
                                        <span class="px-2 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold">Published</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">Draft</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('organizations.show', $organization) }}" class="text-primary font-medium hover:underline">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-soft px-5 py-10 text-center text-gray-400">
            Belum ada organisasi terdaftar.
        </div>
    @endforelse
@endsection
