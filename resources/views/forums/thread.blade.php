@extends('layouts.app')

@section('title') Forum :: {{ $thread->title }} @endsection

@section('content')
{!! breadcrumbs(['Forum' => 'forum' , $thread->commentable->name => 'forum/'.$thread->commentable->id, $thread->title => 'forum/'.$thread->commentable->id.'/'.$thread->id ]) !!}

<div class="row no-gutters">

    <h1 class="col-md">
        {!! $thread->displayName !!}
        <a href="{{ url('reports/new?url=') . $thread->threadUrl }}"><i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Click here to report this thread." style="opacity: 50%;font-size:0.7em; float:right;"></i></a>
    </h1>

    @auth
        @can('reply-to-comment', $thread)
            <div class="col-md text-right">
                <button data-toggle="modal" data-target="#reply-modal-{{ $thread->getKey() }}" class="btn px-3 py-2 px-sm-4 py-sm-1 btn-primary text-uppercase"><i class="fas fa-comment"></i><span class="ml-2 d-none d-sm-inline-block">Reply to Thread</span></button>
            </div>
        @endcan
    @endauth
</div>

@inject('markdown', 'Parsedown')
@php
    $markdown->setSafeMode(true);
@endphp




<div class="border mb-2 row no-gutters" style="border-style:double!important; border-width:3px!important;clear:both;">
    <div class="col-md-2 text-center border-md-right border-bottom border-md-bottom-0">
        <img class="mt-2 mw-100" src="/images/avatars/{{ $thread->commenter->avatar }}" style="max-width:100px; max-height:100px; border-radius:50%;" alt="{{ $thread->commenter->name }} Avatar">
        <h5>{!! $thread->commenter->displayName !!}</h5>
        <p>@auth <a href="{{ $thread->commenter->url}}/forum"> @endauth{!! $thread->commenter->forumCount !!} Posts @auth </a>@endauth</p>
    </div>
    <div class="col-md">
        <div class="mb-2 border-bottom p-2">
            <div class="row no-gutters justify-content-between">
                <div class="col">
                    @if($thread->type == "User-User")
                        <a href="{{ url('comment/').'/'.$thread->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                    @endif
                    {!! $thread->created_at->calendar() !!}
                    @if($thread->created_at != $thread->updated_at)
                        <small><span class="text-muted border-left mx-1 px-1">Edited {!! ($thread->updated_at->calendar()) !!}</span></small>
                    @endif
                </div>
                <div class="col text-right">
                @if(Auth::check())
                    @can('reply-to-comment', $thread)
                        <a role="button" data-toggle="modal" data-target="#reply-modal-{{ $thread->getKey() }}" class="px-2 py-2 px-sm-2 py-sm-1 text-uppercase" style="cursor: pointer;"><i class="fas fa-comment"></i><span class="ml-2 d-none d-sm-inline-block">Reply</span></a>
                    @endcan
                    @can('edit-comment', $thread)
                        <a href="{!! $thread->threadUrl.'/edit' !!}" class="px-2 py-2 px-sm-2 py-sm-1 text-uppercase" style="cursor: pointer;"><i class="fas fa-edit"></i><span class="ml-2 d-none d-sm-inline-block">Edit</span></a>
                    @endcan
                    @can('delete-comment', $thread)
                        <a role="button" data-toggle="modal" data-target="#delete-modal-{{ $thread->getKey() }}" class="px-2 py-2 px-sm-2 py-sm-1 text-danger text-uppercase" style="cursor: pointer;"><i class="fas fa-minus-circle"></i><span class="ml-2 d-none d-sm-inline-block">Delete</span></a>
                    @endcan
                @endif
                </div>
            </div>
        </div>
        <div class="p-2">
            <p>{!! nl2br($thread->comment) !!}</p>
        </div>
    </div>
</div>

@include('forums._form_modals', ['comment' => $thread])



@if($replies->count())
    {!! $replies->render() !!}
    @foreach($replies as $comment)
        @if(!isset($comment->deleted_at))
            <div class="border mb-2 row no-gutters">
                <div class="col-md-3 text-center border-md-right border-bottom border-md-bottom-0">
                    <img class="mt-2 mw-100" src="/images/avatars/{{ $comment->commenter->avatar }}" style="max-width:100px; max-height:100px; border-radius:50%;" alt="{{ $comment->commenter->name }} Avatar">
                    <h5>{!! $comment->commenter->displayName !!}</h5>
                    <p>@auth <a href="{{ $comment->commenter->url}}/forum"> @endauth{!! $comment->commenter->forumCount !!} Posts @auth </a>@endauth</p>
                </div>
                <div class="col-md">
                    <div class="mb-2 border-bottom p-2">
                        <div class="row no-gutters justify-content-between">
                            <div class="col">
                                @if($comment->type == "User-User")
                                    <a href="{{ url('comment/').'/'.$comment->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                                @endif
                                {!! $comment->created_at->calendar() !!}
                                @if($comment->created_at != $comment->updated_at)
                                    <small><span class="text-muted border-left mx-1 px-1">Edited {!! ($comment->updated_at->calendar()) !!}</span></small>
                                @endif
                            </div>
                            <div class="col text-right">
                            @if(Auth::check())
                                @can('reply-to-comment', $comment)
                                    <a role="button" data-toggle="modal" data-target="#reply-modal-{{ $comment->getKey() }}" class="px-2 py-2 px-sm-2 py-sm-1 text-uppercase" style="cursor: pointer;"><i class="fas fa-comment"></i><span class="ml-2 d-none d-sm-inline-block">Reply</span></a>
                                @endcan
                                @can('edit-comment', $comment)
                                    <a role="button" data-toggle="modal" data-target="#comment-modal-{{ $comment->getKey() }}" class="px-2 py-2 px-sm-2 py-sm-1 text-uppercase" style="cursor: pointer;"><i class="fas fa-edit"></i><span class="ml-2 d-none d-sm-inline-block">Edit</span></a>
                                @endcan
                                @can('delete-comment', $comment)
                                    <a role="button" data-toggle="modal" data-target="#delete-modal-{{ $comment->getKey() }}" class="px-2 py-2 px-sm-2 py-sm-1 text-danger text-uppercase" style="cursor: pointer;"><i class="fas fa-minus-circle"></i><span class="ml-2 d-none d-sm-inline-block">Delete</span></a>
                                @endcan
                                <a href="{{ url('reports/new?url=') . $comment->url }}"><i class="fas fa-exclamation-triangle mr-2" data-toggle="tooltip" title="Click here to report this comment." style="opacity: 50%;"></i></a>
                            @endif
                            </div>
                        </div>
                    </div>
                    @if(isset($comment->data)) @php $data = json_decode($comment->data, true) @endphp @else @php $data = null @endphp @endif
                    <div class="p-2 row justify-content-between">
                        <div class="col-{{ isset($data['dice']) ? 10 : 12 }}">
                            <p>{!! nl2br($markdown->line($comment->comment)) !!}</p>
                        </div>
                        @if(isset($data['dice'])) 
                        <div class="col-2 text-center border-left">
                            <i class="fas fa-dice fa-2x"></i>
                            <p><b>Dice Rolled:</b> <br>{{ $data['dice_type'] }}-Sided Die <br><small class="text-muted">Rolled {{ count($data['dice']) }} @if(count($data['dice']) > 1)  times @else time @endif</small></p>
                            <p><b>Results:</b> @foreach($data['dice'] as $result) <br> {{$result}} @endforeach
                        </div>
                        @endif
                    </div>

                    @include('forums._form_modals', ['comment' => $comment])
                </div>
            </div>
        @endif
    @endforeach
    {!! $replies->render() !!}
@else
    <div class="text-center mb-2"><small>No Replies Yet</small></div>
@endif

@can('reply-to-comment', $thread)
    <div class="card p-3">
        <form method="POST" action="{{ route('comments.reply', $thread->getKey()) }}">
            @csrf
            <h5 class="modal-title">Reply to Thread</h5>
            <div class="form-group mb-0">
                <label for="message">Enter your message here:</label>
                <textarea required class="form-control" name="message" rows="3"></textarea>
                <button class="btn mt-2 btn-outline-primary" type="button" data-toggle="collapse" data-target="#action-collapse" aria-expanded="false" aria-controls="action-collapse">
                    Action?
                  </button>
                <div class="collapse" id="action-collapse">
                    <div class="row col-12">
                        <div class="col-6">
                        <label class="mt-1" for="action">Select Action:</label>
                        <select class="form-control" name="action" id="action">
                            <option value="none">No Action</option>
                            <option value="4">Four-Sided Dice</option>
                            <option value="6">Six-Sided Dice</option>
                            <option value="8">Eight-Sided Dice</option>
                            <option value="10">Ten-Sided Dice</option>
                            <option value="12">Twelve-Sided Dice</option>
                            <option value="20">Twenty-Sided Dice</option>
                            <option value="100">Hundred-Sided Dice</option>
                        </select>
                        </div>
                        <div class="col-6">
                            <label class="mt-1" for="quantity">Input Number of Rolls:</label>
                            <input type="text" id="quantity" type="number" min="1" class="form-control" name="quantity" placeholder="Input Integer Quantity">
                        </div>
                    </div>
                </div>
                <small class="form-text text-muted"><a target="_blank" href="https://help.github.com/articles/basic-writing-and-formatting-syntax">Markdown cheatsheet.</a></small>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-sm px-md-4 btn-outline-secondary text-uppercase" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm px-md-4 btn-outline-success text-uppercase">Reply</button>
            </div>
        </form>
    </div>
@endcan

@if(Auth::check() && Auth::user()->hasPower('edit_data'))
    <div class="mt-2 d-flex justify-content-end">
        <div class="my-auto mr-2"><strong>ADMIN:</strong> </div>
        <button data-toggle="modal" data-target="#lock-modal-{{ $thread->getKey() }}" class="btn btn-sm btn-primary mx-1 text-uppercase"><i class="fas fa-lock"></i><span class="ml-2 d-none d-sm-inline-block">{{ $thread->is_locked ? 'Unlock' : 'Lock'}} Thread</span></button>
        <button data-toggle="modal" data-target="#pin-modal-{{ $thread->getKey() }}" class="btn btn-sm btn-primary mx-1 text-uppercase"><i class="fas fa-thumbtack"></i><span class="ml-2 d-none d-sm-inline-block">{{ $thread->is_featured ? 'Unpin' : 'Pin'}} Thread</span></button>
    </div>
@endif

@endsection
