<h1>Seed Settings</h1>

{!! Form::label('Waterings') !!}
{!! Form::number('waterings', $tag->getData()['waterings'], ['class' => 'form-control mb-1', 'min' => 1]) !!}

<div class="form-group">
    {!! Form::label('Stage 1 Img') !!} 
    <div>{!! Form::file('image_1') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
</div>

<div class="form-group">
    {!! Form::label('Stage 2 Img') !!}
    <div>{!! Form::file('images_2') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
</div>

<div class="form-group">
    {!! Form::label('Stage 3 Img') !!}
    <div>{!! Form::file('image_3') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
</div>

{!! Form::hidden('item', $item) !!}