@extends('patients::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('patients.name') !!}</p>
@endsection
