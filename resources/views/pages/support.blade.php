@extends('layouts.marketing')

@section('title', 'Support')

@section('content')
<section class="max-w-marketing mx-auto px-5 sm:px-6 py-16">
    <h1 class="font-display text-3xl font-semibold text-white mb-4">Support</h1>
    <p class="text-slate-400 text-lg mb-8">Use the Help Center or Contact page to reach our team. Signed-in users can open support tickets from the dashboard.</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('help') }}" class="px-5 py-3 rounded-xl bg-primary text-white font-semibold">Help Center</a>
        <a href="{{ route('contact') }}" class="px-5 py-3 rounded-xl border border-border-subtle text-white font-semibold">Contact Us</a>
        <a href="{{ route('dashboard.support.create') }}" class="px-5 py-3 rounded-xl border border-border-subtle text-white font-semibold">Open a ticket</a>
    </div>
</section>
@endsection
