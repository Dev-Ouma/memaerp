@extends('layouts.app')
@section('title', 'Admin Setups')
@section('section', 'Administration · Setups')
@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">Platform administration</div>
        <h1 class="heading">Admin Setups</h1>
        <p class="sub">Configure each ERP module from one central administration workspace.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Module setups</h2>
            <small>Choose a module to manage its governed configuration.</small>
        </div>
    </div>
    <div class="panel-body">
        <div class="grid4">
            <article class="stat">
                <div class="stat-head"><span>Admissions setups</span><i data-lucide="clipboard-list"></i></div>
                <b>{{ $admissionsSummary['total'] }}</b>
                <small>{{ $admissionsSummary['active'] }} active configurations</small>
                <div style="margin-top:16px">
                    <a class="btn btn-secondary" href="{{ route('admissions.setups.index') }}">Manage admissions <i data-lucide="arrow-right"></i></a>
                </div>
            </article>

            <article class="stat">
                <div class="stat-head"><span>Data governance</span><i data-lucide="shield-check"></i></div>
                <b>Controlled</b>
                <small>Retention, legal holds, purge approvals and audit evidence</small>
                <div style="margin-top:16px">
                    <a class="btn btn-secondary" href="{{ route('admin.setups.governance.index') }}">Manage governance <i data-lucide="arrow-right"></i></a>
                </div>
            </article>

            <article class="stat">
                <div class="stat-head"><span>Access control</span><i data-lucide="key-round"></i></div>
                <b>Scoped</b>
                <small>Roles, permissions, expiry and assignment audit</small>
                <div style="margin-top:16px">
                    <a class="btn btn-secondary" href="{{ route('admin.setups.access.index') }}">Manage access <i data-lucide="arrow-right"></i></a>
                </div>
            </article>

            @foreach (['Academics'] as $module)
                <article class="stat" style="opacity:.7">
                    <div class="stat-head"><span>{{ $module }} setups</span><i data-lucide="construction"></i></div>
                    <b>—</b>
                    <small>Available when the module is introduced</small>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
