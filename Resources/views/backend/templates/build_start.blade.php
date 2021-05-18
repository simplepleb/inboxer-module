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

            <div class="row mt-4">

                    <div class="row">
                        @foreach(Modules\Inboxer\Entities\Template::templateStyles() as $name => $style)
                            <div class="col-sm-12 col-xs-6 col-sm-3 col-md-2">
                                <a href="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@build', ['style' => $name]) }}">
                                    <div class="card panel-flat panel-template-style">
                                        <div class="card-body">
                                            <img src="{{ url('/vendor/simplepleb/marketing/public/images/template_styles/'.$name.'.png') }}" />
                                            <h5 class="mb-0 text-center">{{ trans('inboxer::messages.'.$name) }}</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>


            </div>
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

