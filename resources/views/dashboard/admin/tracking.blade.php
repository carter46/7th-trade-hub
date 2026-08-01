@extends('layouts.dashboard-admin')

@section('title', 'Marketing & Tracking')

@section('content')
<x-layout.page
    title="Marketing & Tracking"
    subtitle="Official integrations by ID, verification tags, and named custom scripts."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Settings', route('admin.settings')],
        ['Marketing & Tracking', null],
    ]"
>
    <div class="space-y-6">
        @if (! empty($duplicateConflicts))
            <x-dashboard.alert variant="warning" title="Possible duplicate tracking">
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($duplicateConflicts as $conflict)
                        <li>
                            “{{ $conflict['script_name'] }}” — {{ $conflict['message'] }}
                        </li>
                    @endforeach
                </ul>
            </x-dashboard.alert>
        @endif

        @error('tracking_connection')
            <p class="text-sm text-danger">{{ $message }}</p>
        @enderror

        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Supported integrations</h2>
            <p class="text-sm text-text-secondary mb-4">Enter IDs only. Laravel renders the official snippets in the correct order.</p>

            <form method="POST" action="{{ route('admin.tracking.providers') }}" class="space-y-6">
                @csrf

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Google Tag Manager</h3>
                    <input type="hidden" name="gtm_enabled" value="0">
                    <x-dashboard.toggle name="gtm_enabled" label="Enable Google Tag Manager" :checked="old('gtm_enabled', $gtm->enabled)" value="1" />
                    <x-dashboard.input name="gtm_container_id" label="Container ID" :value="old('gtm_container_id', $gtm->credential('container_id'))" hint="Format: GTM-XXXXXXX" />
                </div>

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Google Analytics</h3>
                    <input type="hidden" name="google_enabled" value="0">
                    <x-dashboard.toggle name="google_enabled" label="Enable Google Analytics" :checked="old('google_enabled', $ga->enabled)" value="1" />
                    <x-dashboard.input name="google_measurement_id" label="Measurement ID" :value="old('google_measurement_id', $ga->credential('measurement_id'))" hint="Format: G-XXXXXXXXXX" />
                    <x-dashboard.input name="google_property_id" label="Property ID (optional)" :value="old('google_property_id', $ga->credential('property_id'))" />
                </div>

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Microsoft Clarity</h3>
                    <input type="hidden" name="clarity_enabled" value="0">
                    <x-dashboard.toggle name="clarity_enabled" label="Enable Microsoft Clarity" :checked="old('clarity_enabled', $clarity->enabled)" value="1" />
                    <x-dashboard.input name="clarity_project_id" label="Project ID" :value="old('clarity_project_id', $clarity->credential('project_id'))" />
                </div>

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Meta Pixel</h3>
                    <input type="hidden" name="meta_enabled" value="0">
                    <x-dashboard.toggle name="meta_enabled" label="Enable Meta Pixel" :checked="old('meta_enabled', $meta->enabled)" value="1" />
                    <x-dashboard.input name="meta_pixel_id" label="Pixel ID" :value="old('meta_pixel_id', $meta->credential('pixel_id'))" hint="Digits only" />
                </div>

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Site verification</h3>
                    <x-dashboard.input name="verification_google" label="Google site verification" :value="old('verification_google', $verificationGoogle)" hint="Content value only (not the full meta tag)." />
                    <x-dashboard.input name="verification_bing" label="Bing webmaster verification" :value="old('verification_bing', $verificationBing)" />
                    <x-dashboard.input name="verification_facebook" label="Facebook domain verification" :value="old('verification_facebook', $verificationFacebook)" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-dashboard.button type="submit" variant="primary">Save marketing & tracking</x-dashboard.button>
                    <x-dashboard.button type="submit" variant="secondary" formaction="{{ route('admin.tracking.test') }}" name="provider" value="google_tag_manager">Test GTM ID</x-dashboard.button>
                    <x-dashboard.button type="submit" variant="secondary" formaction="{{ route('admin.tracking.test') }}" name="provider" value="google_analytics">Test GA ID</x-dashboard.button>
                    <x-dashboard.button type="submit" variant="secondary" formaction="{{ route('admin.tracking.test') }}" name="provider" value="microsoft_clarity">Test Clarity ID</x-dashboard.button>
                    <x-dashboard.button type="submit" variant="secondary" formaction="{{ route('admin.tracking.test') }}" name="provider" value="meta_pixel">Test Meta Pixel ID</x-dashboard.button>
                </div>
            </form>
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-text-primary">Custom scripts</h2>
                    <p class="text-sm text-text-secondary">Named scripts for TikTok, Hotjar, widgets, or anything without a dedicated field.</p>
                </div>
            </div>

            <div class="space-y-3 mb-8">
                @forelse ($scripts as $script)
                    <div class="rounded-xl border border-border-subtle p-4" x-data="{ editing: false }">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-text-primary">{{ $script->name }}</p>
                                    <x-dashboard.badge :status="$script->enabled ? 'completed' : 'neutral'">
                                        {{ $script->enabled ? 'Enabled' : 'Disabled' }}
                                    </x-dashboard.badge>
                                    <span class="text-xs text-text-muted">{{ str_replace('_', ' ', $script->location) }}</span>
                                    @foreach (($duplicateLabels[$script->id] ?? []) as $label)
                                        <x-dashboard.badge status="warning">Possible duplicate of {{ $label }}</x-dashboard.badge>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-dashboard.button type="button" variant="secondary" size="sm" @click="editing = !editing">Edit</x-dashboard.button>
                                <form method="POST" action="{{ route('admin.tracking.scripts.destroy', $script) }}" onsubmit="return confirm('Delete this custom script?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-dashboard.button type="submit" variant="danger" size="sm">Delete</x-dashboard.button>
                                </form>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('admin.tracking.scripts.update', $script) }}"
                            class="mt-4 space-y-3 border-t border-border-subtle pt-4"
                            x-show="editing"
                            x-cloak
                        >
                            @csrf
                            @method('PUT')
                            <x-dashboard.input name="name" label="Name" :value="old('name', $script->name)" required />
                            <div>
                                <label class="mb-1 block text-sm font-medium text-text-secondary">Location</label>
                                <div class="flex flex-wrap gap-4 text-sm">
                                    @foreach (['head' => 'Head', 'body_start' => 'Body start', 'body_end' => 'Body end'] as $value => $label)
                                        <label class="inline-flex items-center gap-2">
                                            <input type="radio" name="location" value="{{ $value }}" @checked(old('location', $script->location) === $value)>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="enabled" value="0">
                            <x-dashboard.toggle name="enabled" label="Enabled" :checked="old('enabled', $script->enabled)" value="1" />
                            <div>
                                <label class="mb-1 block text-sm font-medium text-text-secondary">Code</label>
                                <textarea name="code" rows="6" class="w-full rounded-xl border-border-default bg-elevated font-mono text-xs" required>{{ old('code', $script->code) }}</textarea>
                            </div>
                            <x-dashboard.button type="submit" size="sm">Update script</x-dashboard.button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-text-muted">No custom scripts yet.</p>
                @endforelse
            </div>

            <div class="rounded-xl border border-border-subtle p-4 space-y-4" x-data="{ open: {{ $errors->hasAny(['name', 'location', 'code']) ? 'true' : 'false' }} }">
                <button type="button" class="text-base font-semibold text-text-primary" @click="open = !open">
                    + Add script
                </button>
                <form method="POST" action="{{ route('admin.tracking.scripts.store') }}" class="space-y-4" x-show="open" x-cloak>
                    @csrf
                    <x-dashboard.input name="name" label="Name" :value="old('name')" required />
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text-secondary">Location</label>
                        <div class="flex flex-wrap gap-4 text-sm">
                            @foreach (['head' => 'Head', 'body_start' => 'Body start', 'body_end' => 'Body end'] as $value => $label)
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="location" value="{{ $value }}" @checked(old('location', 'head') === $value)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('location')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
                    </div>
                    <input type="hidden" name="enabled" value="0">
                    <x-dashboard.toggle name="enabled" label="Enabled" :checked="old('enabled', true)" value="1" />
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text-secondary">Code</label>
                        <textarea name="code" rows="8" class="w-full rounded-xl border-border-default bg-elevated font-mono text-xs" required placeholder="Paste the third-party snippet here">{{ old('code') }}</textarea>
                        @error('code')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
                    </div>
                    <x-dashboard.button type="submit" variant="primary">Save script</x-dashboard.button>
                </form>
            </div>
        </x-dashboard.card>

        <x-dashboard.card variant="solid" x-data="{ showHtml: false }">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Preview rendered output</h2>
            <p class="text-sm text-text-secondary mb-4">What tracking tags inject on public marketing and auth pages. Live chat is marketing-only and is listed for order reference (not part of compiled HTML).</p>

            @foreach ([
                'head' => '<head>',
                'body_start' => '<body> start',
                'body_end' => '</body> end',
            ] as $section => $heading)
                <div class="mb-4">
                    <h3 class="mb-2 text-sm font-semibold text-text-primary">{{ $heading }}</h3>
                    @if (empty($preview[$section]))
                        <p class="text-sm text-text-muted">Nothing in this section.</p>
                    @else
                        <ul class="space-y-1 text-sm">
                            @foreach ($preview[$section] as $item)
                                <li class="flex flex-wrap items-center gap-2">
                                    <span class="text-success">✓</span>
                                    <span class="text-text-primary">{{ $item['label'] }}</span>
                                    <span class="text-xs text-text-muted">{{ $item['source'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <x-dashboard.button type="button" variant="secondary" size="sm" @click="showHtml = !showHtml">
                <span x-text="showHtml ? 'Hide compiled HTML' : 'Show compiled HTML'"></span>
            </x-dashboard.button>

            <div class="mt-4 space-y-4" x-show="showHtml" x-cloak>
                @foreach (['head' => 'Head HTML', 'body_start' => 'Body start HTML', 'body_end' => 'Body end HTML'] as $key => $label)
                    <div>
                        <p class="mb-1 text-xs font-medium text-text-secondary">{{ $label }}</p>
                        <pre class="overflow-x-auto rounded-xl border border-border-subtle bg-muted/40 p-3 text-xs text-text-secondary whitespace-pre-wrap break-words">{{ $preview['html'][$key] !== '' ? $preview['html'][$key] : '<!-- empty -->' }}</pre>
                    </div>
                @endforeach
            </div>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
