@extends('jiny.admin::layouts.admin')

@section('title', '국가 통계')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">국가 통계</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.system.countries.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> 목록으로
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total'] }}</h3>
                                    <p>전체 국가</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['active'] }}</h3>
                                    <p>활성 국가</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['inactive'] }}</h3>
                                    <p>비활성 국가</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $stats['default'] }}</h3>
                                    <p>기본 국가</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">활성화 비율</h5>
                                </div>
                                <div class="card-body">
                                    <div class="progress">
                                        @if($stats['total'] > 0)
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['active'] / $stats['total']) * 100 }}%">
                                                {{ round(($stats['active'] / $stats['total']) * 100, 1) }}%
                                            </div>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        활성: {{ $stats['active'] }}개 / 전체: {{ $stats['total'] }}개
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">기본 국가 설정</h5>
                                </div>
                                <div class="card-body">
                                    @if($stats['default'] > 0)
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            기본 국가가 설정되어 있습니다.
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            기본 국가가 설정되지 않았습니다.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">요약 정보</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            전체 국가 수
                                            <span class="badge badge-primary badge-pill">{{ $stats['total'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            활성 국가 수
                                            <span class="badge badge-success badge-pill">{{ $stats['active'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            비활성 국가 수
                                            <span class="badge badge-warning badge-pill">{{ $stats['inactive'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            기본 국가 수
                                            <span class="badge badge-info badge-pill">{{ $stats['default'] }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
