
@extends('backend.layouts.app')

@section('title', $list->name . ": " . trans('marketing::messages.create_subscriber'))


@section('content')
	<div class="wrapper">

		<section class="content" id="mkting">

			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><i class="fa fa-plus"></i> {{ trans('inboxer::messages.create_subscriber') }}</div>
                        <div class="card-body">
                            <form action="{{ action('\Modules\Inboxer\Http\Controllers\Backend\SubscriberController@store', $list->uid) }}" method="POST" class="form-validate-jquery">
                                {{ csrf_field() }}

                                @include("inboxer::backend.subscribers._form")

                                {{--@if (\Modules\Inboxer\Entities\Setting::get('import_subscribers_commitment'))
                                    <hr>
                                    <div class="mt-40">
                                        @include('inboxer::helpers.form_control', [
                                            'type' => 'checkbox2',
                                            'class' => 'policy_commitment mb-10 required',
                                            'name' => 'policy_commitment',
                                            'value' => 'no',
                                            'required' => true,
                                            'label' => \Modules\Inboxer\Entities\Setting::get('import_subscribers_commitment'),
                                            'options' => ['no','yes'],
                                            'rules' => []
                                        ])
                                    </div>
                                @endif--}}
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
                                {{--<div class="text-left">
                                    <button class="btn btn-md btn-primary mr-10"><i class="fa fa-check"></i> {{ trans('inboxer::messages.save') }}</button>
                                    <a href="{{ action('\Modules\Inboxer\Http\Controllers\Backend\SubscriberController@index', $list->uid) }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('inboxer::messages.cancel') }}</a>
                                </div>--}}
                                <form>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

@endsection



