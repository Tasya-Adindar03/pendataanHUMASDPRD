@extends('layouts.app')
@section('title', 'Data Media Online')

@section('content')
    @php($tipe = 'online')
    <div class="card-custom">
        <div class="d-flex justify-content-end mb-3">
            @include('advertorials._export-buttons')
        </div>
        @include('advertorials._filter')
        @include('advertorials._table')
    </div>
@endsection