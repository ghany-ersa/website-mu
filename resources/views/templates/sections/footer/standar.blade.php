{{--
    Locked section - always present, always last, not user-editable (see config/page-builder.php
    and OrganizationPage::ensureFooter()/sectionsInDisplayOrder()). Renders both in tenant-page
    context ($organization set, template-preview context has none) and template-preview context
    ($template set instead) - every field below falls back gracefully when $organization is absent.

    Trust-building layout: prominent logo/name, verifiable contact channels (each a real tel:/
    wa.me/mailto/maps link, not decoration), and social proof via Instagram/Facebook when set —
    all signal "this is a real, reachable organization" to a first-time visitor.

    The "Dibuat dengan website-mu.id" badge is deliberate platform branding/attribution, not
    editable content - it stays regardless of what content fields this section does or doesn't
    have, by the same lock that keeps the rest of the footer from being deleted. It's hidden
    only for organizations on a plan with hide_branding (see Plan::hide_branding) - a paid
    entitlement, not something the org can toggle itself.
--}}
@php
    $orgName = $section['content']['org_name']
        ?? $template->structure['sample_org_name'] ?? null
        ?? $organization->name ?? null
        ?? '[Nama Organisasi]';
    $orgLogo = $organization->logo ?? null;
    $phone = $organization->phone ?? null;
    $email = $organization->email ?? null;
    $whatsapp = $organization->whatsapp ?? null;
    $address = $organization->address ?? null;
    $instagramUrl = $organization->instagram_url ?? null;
    $facebookUrl = $organization->facebook_url ?? null;
    $whatsappHref = \App\Services\WhatsAppNumber::href($whatsapp);
    $hasSocial = $instagramUrl || $facebookUrl;
    $hideBranding = $organization->plan?->hide_branding ?? false;
@endphp

<footer class="bg-primary text-white/70" style="background-image: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35))">
    <div class="max-w-6xl mx-auto px-6 py-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.3fr_1fr]">
        <div>
            <div class="flex items-center gap-3 mb-4">
                @if ($orgLogo)
                    <img src="{{ $orgLogo }}" alt="{{ $orgName }}" class="w-12 h-12 rounded-brand object-contain ring-white/10">
                @else
                    <div class="w-12 h-12 rounded-brand bg-primary flex items-center justify-center text-white font-bold text-lg shrink-0">
                        {{ mb_substr($orgName, 0, 1) }}
                    </div>
                @endif
                <span class="text-white font-extrabold text-lg leading-tight">{{ $orgName }}</span>
            </div>

            @if ($hasSocial)
                <div class="flex items-center gap-2.5">
                    @if ($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" aria-label="Instagram {{ $orgName }}"
                           class="w-9 h-9 rounded-brand bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.24 2.22.4.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.35 1.05.4 2.22.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.24 1.8-.4 2.22a3.7 3.7 0 0 1-.9 1.38 3.7 3.7 0 0 1-1.38.9c-.42.16-1.05.35-2.22.4-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.24-2.22-.4a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.35-1.05-.4-2.22-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.24-1.8.4-2.22.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.05-.35 2.22-.4C8.42 2.17 8.8 2.16 12 2.16Zm0 1.98c-3.14 0-3.5.01-4.74.07-.95.04-1.47.2-1.81.34-.46.18-.78.39-1.13.73-.34.35-.55.67-.73 1.13-.14.34-.3.86-.34 1.81-.06 1.24-.07 1.6-.07 4.74s.01 3.5.07 4.74c.04.95.2 1.47.34 1.81.18.46.39.78.73 1.13.35.34.67.55 1.13.73.34.14.86.3 1.81.34 1.24.06 1.6.07 4.74.07s3.5-.01 4.74-.07c.95-.04 1.47-.2 1.81-.34.46-.18.78-.39 1.13-.73.34-.35.55-.67.73-1.13.14-.34.3-.86.34-1.81.06-1.24.07-1.6.07-4.74s-.01-3.5-.07-4.74c-.04-.95-.2-1.47-.34-1.81a3.03 3.03 0 0 0-.73-1.13 3.03 3.03 0 0 0-1.13-.73c-.34-.14-.86-.3-1.81-.34-1.24-.06-1.6-.07-4.74-.07Zm0 3.37a5.49 5.49 0 1 1 0 10.98 5.49 5.49 0 0 1 0-10.98Zm0 9.05a3.56 3.56 0 1 0 0-7.12 3.56 3.56 0 0 0 0 7.12Zm6.99-9.27a1.28 1.28 0 1 1-2.57 0 1.28 1.28 0 0 1 2.57 0Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                    @if ($facebookUrl)
                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" aria-label="Facebook {{ $orgName }}"
                           class="w-9 h-9 rounded-brand bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.08 5.66 21.24 10.44 22v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.77l-.44 2.91h-2.33V22C18.34 21.24 22 17.08 22 12.06Z" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <p class="text-white font-semibold text-sm mb-3.5">Hubungi Kami</p>
            <ul class="space-y-3 text-sm">
                @if ($address)
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 mt-0.5 text-secondary">
                            <path fill-rule="evenodd" d="M9.69 18.933a.75.75 0 0 0 .62 0c.058-.026.157-.079.29-.161a20.6 20.6 0 0 0 2.968-2.34C15.462 14.518 17.5 11.87 17.5 8.75c0-4.18-3.32-7.5-7.5-7.5s-7.5 3.32-7.5 7.5c0 3.12 2.038 5.769 3.933 7.68a20.6 20.6 0 0 0 3.257 2.502ZM10 11.25a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $address }}</span>
                    </li>
                @endif
                @if ($phone)
                    <li class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-secondary">
                            <path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-.826 1.667l-.94.47a.5.5 0 0 0-.223.667c.435.868 1.42 2.442 2.85 3.872 1.43 1.43 3.004 2.415 3.872 2.85a.5.5 0 0 0 .667-.223l.47-.94a1.5 1.5 0 0 1 1.667-.826l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43a13.022 13.022 0 0 1-8.744-8.744A13.014 13.014 0 0 1 2.5 5H2v-1.5Z" />
                        </svg>
                        <a href="tel:{{ $phone }}" class="hover:text-white transition-colors">{{ $phone }}</a>
                    </li>
                @endif
                @if ($whatsapp)
                    <li class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 shrink-0 text-secondary">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.95 6.45 17.5 2 12.04 2Zm0 18.13a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.36c0-4.54 3.7-8.22 8.24-8.22 4.54 0 8.23 3.68 8.23 8.22 0 4.55-3.69 8.22-8.23 8.22Zm4.52-6.16c-.25-.12-1.46-.72-1.68-.8-.23-.08-.39-.12-.56.12-.16.25-.64.8-.79.96-.14.16-.29.18-.54.06-.25-.12-1.04-.38-1.99-1.22-.73-.66-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.65.31-.23.25-.85.83-.85 2.03s.87 2.36.99 2.52c.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.46-.6 1.66-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z" />
                        </svg>
                        <a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="hover:text-white transition-colors">{{ $whatsapp }}</a>
                    </li>
                @endif
                @if ($email)
                    <li class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-secondary">
                            <path d="M3 4a2 2 0 0 0-2 2v.01L10 12l9-5.99V6a2 2 0 0 0-2-2H3Z" />
                            <path d="M18 8.24l-7.386 4.924a1 1 0 0 1-1.228 0L2 8.24V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.24Z" />
                        </svg>
                        <a href="mailto:{{ $email }}" class="hover:text-white transition-colors break-all">{{ $email }}</a>
                    </li>
                @endif
                @if (! $address && ! $phone && ! $whatsapp && ! $email)
                    <li class="text-white/50">Informasi kontak belum ditambahkan.</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/60">
            <span>&copy; {{ date('Y') }} {{ $orgName }}. Seluruh hak cipta dilindungi.</span>
            @unless ($hideBranding)
                <a href="https://website-mu.id" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-brand bg-white/10 text-white/70 hover:bg-white/20 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-secondary">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                    </svg>
                    Dibuat dengan
                    <span class="font-semibold text-white">website-mu.id</span>
                </a>
            @endunless
        </div>
    </div>
</footer>
