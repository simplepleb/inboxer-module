
@extends('backend.layouts.app')

@section('title', $list->name . ": " . trans('marketing::messages.create_subscriber'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('/vendor/simplepleb/marketing/assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/vendor/simplepleb/marketing/assets/js/plugins/pickers/anytime.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('/vendor/simplepleb/marketing/public/js/validate.js') }}"></script>
@endsection

@section('page_header')

    @include("marketing::lists._header")

@endsection

@section('content')
    <div class="wrapper">
        @include('marketing::components.navigation')
        <section class="content" id="mkting">
            @yield('page_header')
            @include("marketing::lists._menu")

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="sub-section">
                        <form enctype="multipart/form-data"  action="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@update', ['list_uid' => $list->uid, "uid" => $subscriber->uid]) }}" method="POST" class="form-validate-jqueryz">
                            {{ csrf_field() }}
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="list_uid" value="{{ $list->uid }}" />
                            @include('marketing::helpers._upload',['src' => action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@avatar',  $subscriber->uid), 'dragId' => 'upload-avatar', 'preview' => 'image'])
                            <h3 class="clear-both">{{trans("messages.basic_information")}}</h3>
                            @include("marketing::subscribers._form")

                            <button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('marketing::messages.save') }}</button>
                            <a href="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@index', $list->uid) }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('marketing::messages.cancel') }}</a>

                        </form>
                    </div>

                    <div class="sub-section">
                        <h3 class="text-semibold">{{ trans('marketing::messages.verification.title.email_verification') }}</h3>

                        @if (is_null($subscriber->emailVerification))
                            <p>{!! trans('marketing::messages.verification.wording.verify', [ 'email' => sprintf("<strong>%s</strong>", $subscriber->email) ]) !!}</p>
                            <form enctype="multipart/form-data" action="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@startVerification', ['uid' => $subscriber->uid]) }}" method="POST" class="form-validate-jquery">
                                {{ csrf_field() }}

                                <input type="hidden" name="list_uid" value="{{ $list->uid }}" />

                                {{--@include('marketing::helpers.form_control', [
                                    'type' => 'select',
                                    'name' => 'email_verification_server_id',
                                    'value' => '',
                                    'options' => \Auth::user()->customer->emailVerificationServerSelectOptions(),
                                    'help_class' => 'verification',
                                    'rules' => ['email_verification_server_id' => 'required'],
                                    'include_blank' => trans('marketing::messages.select_email_verification_server')
                                ])--}}
                                <div class="text-left">
                                    <button class="btn bg-teal mr-10"> {{ trans('marketing::messages.verification.button.verify') }}</button>
                                </div>
                            </form>
                        @elseif ($subscriber->emailVerification->isDeliverable())
                            <p>{!! trans('marketing::messages.verification.wording.deliverable', [ 'email' => sprintf("<strong>%s</strong>", $subscriber->email), 'at' => sprintf("<strong>%s</strong>", $subscriber->emailVerification->created_at) ]) !!}</p>
                            <form enctype="multipart/form-data" action="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@resetVerification', ['uid' => $subscriber->uid]) }}" method="POST" class="form-validate-jquery">
                                {{ csrf_field() }}
                                <input type="hidden" name="list_uid" value="{{ $list->uid }}" />

                                <div class="text-left">
                                    <button class="btn bg-teal mr-10">{{ trans('marketing::messages.verification.button.reset') }}</button>
                                </div>
                            </form>
                        @elseif ($subscriber->emailVerification->isUndeliverable())
                            <p>{!! trans('marketing::messages.verification.wording.undeliverable', [ 'email' => sprintf("<strong>%s</strong>", $subscriber->email)]) !!}</p>
                            <form enctype="multipart/form-data" action="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@resetVerification', ['uid' => $subscriber->uid]) }}" method="POST" class="form-validate-jquery">
                                <input type="hidden" name="list_uid" value="{{ $list->uid }}" />

                                <div class="text-left">
                                    <button class="btn bg-teal mr-10">{{ trans('marketing::messages.verification.button.reset') }}</button>
                                </div>
                            </form>
                        @else
                            <p>{!! trans('marketing::messages.verification.wording.risky_or_unknown', [ 'email' => sprintf("<strong>%s</strong>", $subscriber->email), 'at' => sprintf("<strong>%s</strong>", $subscriber->emailVerification->created_at), 'result' => sprintf("<strong>%s</strong>", $subscriber->emailVerification->result)]) !!}</p>
                            <form enctype="multipart/form-data" action="{{ action('\Modules\Marketing\Http\Controllers\Backend\SubscriberController@resetVerification', ['uid' => $subscriber->uid]) }}" method="POST" class="form-validate-jquery">
                                <input type="hidden" name="list_uid" value="{{ $list->uid }}" />

                                <div class="text-left">
                                    <button class="btn bg-teal mr-10">{{ trans('marketing::messages.verification.button.reset') }}</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>


        </section>
    </div>
@endsection

@section('mkting_header')
    {{--@include('marketing::layouts._head')--}}
    @include('marketing::layouts._css')
    @include('marketing::layouts._js')
@endsection

@section('mkting_footer')
    @include("marketing::layouts._modals")
@endsection

