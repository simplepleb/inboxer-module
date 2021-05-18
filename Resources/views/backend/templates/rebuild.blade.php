@extends('inboxer::layouts.builder')

@section('title', trans('inboxer::messages.edit_template') . ' ' . $template->name)

@section('content')

        <div class="left">
            <form action="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@update', $template->uid) }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PATCH">

                <input type="text" name="name" value="{{ $template->name }}" class="required" />
                <textarea class="hide template_content" name="content">{{ $template->content }}</textarea>
                <button class="btn btn-primary mr-5">{{ trans('marketing::messages.save') }}</button>
                <a href="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@index') }}" class="btn bg-slate">{{ trans('inboxer::messages.cancel') }}</a>
            </form>
        </div>
        <div class="right">

        </div>

@endsection

@section('template_content')

    {!! $template->content !!}

@endsection
