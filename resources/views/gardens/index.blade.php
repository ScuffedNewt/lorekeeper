@extends('layouts.app')

@section('title') Gardens @endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
    <div>
      <h1>Gardens</h1>
        <div class="row grid-container">
          <div class="col-4 grid-item">1</div>
          <div class="col-4 grid-item">2</div>
          <div class="col-4 grid-item">3</div>
          <div class="col-4 grid-item">4</div>
          <div class="col-4 grid-item">5</div>
          <div class="col-4 grid-item">6</div>
          <div class="col-4 grid-item">7</div>
          <div class="col-4 grid-item">8</div>
          <div class="col-4 grid-item">9</div>
        </div>
    </div>
@endsection