@extends('emails.layouts.transactional')

@section('title', 'Withdrawal bank updated')
@section('heading', 'Withdrawal bank updated')

@section('content')
    <p style="margin:0 0 16px;">Your withdrawal bank has been updated.</p>
    @if ($oldBank)
        <p style="margin:0 0 8px;"><strong>Old bank:</strong> {{ $oldBank->bank_name }} {{ $oldBank->maskedAccountNumber() }}</p>
    @endif
    <p style="margin:0 0 8px;"><strong>New bank:</strong> {{ $newBank->bank_name }} {{ $newBank->maskedAccountNumber() }} ({{ $newBank->verified_name }})</p>
    <p style="margin:0 0 16px;"><strong>Time:</strong> {{ now()->toDateTimeString() }} UTC</p>
    <p style="margin:0;color:#6b7280;font-size:14px;">If this was not you, contact support immediately.</p>
@endsection
