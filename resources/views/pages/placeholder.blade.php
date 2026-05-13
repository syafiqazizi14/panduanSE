@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? 'Halaman';
  $title = $title ?? 'Halaman';
  $description = $description ?? 'Konten belum tersedia.';
  $searchAction = $searchAction ?? '/umkm/verifikasi';
@endphp

@section('content')
  <main id="main-content" class="mx-auto min-h-screen max-w-5xl px-4 py-10 sm:px-6 lg:px-8" tabindex="-1">
    <section class="rounded-3xl border border-black/5 bg-white/80 p-6 shadow-soft backdrop-blur-xl sm:p-8">
      <p class="inline-flex items-center rounded-full bg-forest px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">UMKM Mojokerto</p>
      <h1 class="mt-4 text-3xl font-black tracking-tight text-ink">{{ $title }}</h1>
      <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">{{ $description }}</p>

      <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <a href="{{ $searchAction }}" class="inline-flex items-center justify-center rounded-2xl bg-forest px-5 py-3 text-sm font-bold text-white shadow-soft">Kembali ke Usaha UMKM</a>
        <a href="/" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Ke Beranda</a>
      </div>
    </section>
  </main>
@endsection
