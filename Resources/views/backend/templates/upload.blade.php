@extends('backend.layouts.app')


@section('breadcrumbs')
    <x-backend-breadcrumbs>
        <x-backend-breadcrumb-item route='{{route("backend.$module_name.index")}}' icon='{{ $module_icon }}' >
            {{ $module_title }}
        </x-backend-breadcrumb-item>
        <x-backend-breadcrumb-item type="active">{{ __($module_action) }}</x-backend-breadcrumb-item>
    </x-backend-breadcrumbs>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-8">
                    <h4 class="card-title mb-0">
                        <i class="{{ $module_icon }}"></i> {{ $module_title }} <small class="text-muted">{{ __($module_action) }}</small>
                    </h4>
                    <div class="small text-muted">
                        @lang(":module_name Management Dashboard", ['module_name'=>Str::title($module_name)])
                    </div>
                </div>
                <!--/.col-->
                <div class="col-4">
                    <div class="btn-toolbar float-right" role="toolbar" aria-label="Toolbar with button groups">
                        <a href="{{ route("backend.$module_name.index") }}" class="btn btn-secondary btn-sm ml-1" data-toggle="tooltip" title="{{ $module_title }} List"><i class="fas fa-list-ul"></i> List</a>
                    </div>
                </div>
                <!--/.col-->
            </div>
            <!--/.row-->

            <hr>


            <form enctype="multipart/form-data" action="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@upload') }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            {!! trans('inboxer::messages.template_upload_guide', ["link" => 'https://s3.amazonaws.com/acellemail/newsletter-template-green.zip']) !!}
                        </div>



                            @include('inboxer::backend.common.form_control', ['required' => true, 'type' => 'text', 'label' => trans('inboxer::messages.template_name'), 'name' => 'name', 'value' => old('name'), 'rules' => ['name' => 'required']])

                            @include('inboxer::backend.common.form_control', ['required' => true, 'type' => 'file', 'label' => trans('inboxer::messages.Choose Template'), 'name' => 'file'])

                            {{--<div class="text-right">
                                <button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('inboxer::messages.upload') }}</button>
                                <a href="{{ action('\Modules\Marketing\Http\Controllers\Backend\TemplateController@index') }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('inboxer::messages.cancel') }}</a>
                            </div>--}}



                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            {{ html()->button($text = "<i class='fas fa-plus-circle'></i> " . ucfirst($module_action) . "", $type = 'submit')->class('btn btn-success') }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="float-right">
                            <div class="form-group">
                                <button type="button" class="btn btn-warning" onclick="history.back(-1)"><i class="fas fa-reply"></i> Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>


        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col">

                </div>
            </div>
        </div>
    </div>
    <style>
        .panel-template-style .card-body img {
            max-width: 100%;
        }
    </style>
@stop

