{{-- resources/views/page/show.blade.php --}}
@extends('layouts.app')

@section('title', $page->title . ' - FPCI UNEJ')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/writing.css') }}">

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <div class="post-detail-header">
                <h1 class="post-title">{{ $page->title }}</h1>
                <div class="post-meta">
                    <span><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($page->date_published)->format('F d, Y') }}</span>
                </div>
            </div>
            
            @if($page->featured_image_path)
            <div class="post-featured-image">
                <img src="{{ $page->image_url }}" class="w-100" alt="{{ $page->title }}">
            </div>
            @endif
            
            <div class="post-content">
                {!! $page->content !!}
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection