@extends('layouts.admin')

@section('title', 'Organisasi')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-primary">Organisasi Terdaftar</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $organizations->total() }} organisasi terdaftar.</p>
    </div>

    <x-crud.search-form placeholder="Cari nama, slug, atau owner...">
        <select name="organization_type_id" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-full border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            <option value="">Semua Jenis</option>
            @foreach ($organizationTypes as $type)
                <option value="{{ $type->id }}" @selected(request('organization_type_id') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </x-crud.search-form>

    <div class="bg-white rounded-2xl shadow-soft overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">#</th>
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">Jenis Organisasi</th>
                    <th class="px-5 py-3">Owner</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($organizations as $organization)
                    @php $owner = $organization->members->first(); @endphp
                    <tr>
                        <td class="px-5 py-4 text-gray-400">
                            {{ $organizations->firstItem() + $loop->index }}
                        </td>
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
                            <a href="{{ route('admin.organizations.show', $organization) }}" class="text-primary font-medium hover:underline">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                            @if (request('q') || request('organization_type_id'))
                                Tidak ada organisasi yang cocok dengan pencarian.
                            @else
                                Belum ada organisasi terdaftar.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $organizations->onEachSide(1)->links() }}
    </div>
@endsection
