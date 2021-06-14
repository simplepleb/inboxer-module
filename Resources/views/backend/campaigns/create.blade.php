@extends('backend.layouts.app')

@section('title') {{ __($module_action) }} {{ $module_title }} @endsection

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
                    <i class="{{ $module_icon }}"></i>  {{ $module_title }} <small class="text-muted">{{ __($module_action) }}</small>
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
            <div class="col">
                {{ html()->form('POST', route("backend.$module_name.store"))->class('form')->open() }}

                <div id="wizard">
                    <h3>Step 1 Title</h3>
                    <section>
                        <h5 class="bd-wizard-step-title">Step 1</h5>
                        <h2 class="section-heading">Select campaign type </h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.</p>
                        <div class="purpose-radios-wrapper">
                            <div class="purpose-radio">
                                <input type="radio" name="type" id="branding" class="purpose-radio-input" value="Welcome" checked>
                                <label for="branding" class="purpose-radio-label">
                   <span class="label-icon">
                     <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_branding.svg')}}" alt="welcome" class="label-icon-default">
                     <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_branding_green.svg')}}" alt="welcome" class="label-icon-active">
                   </span>
                                    <span class="label-text">Welcome Series</span>
                                </label>
                            </div>
                            <div class="purpose-radio">
                                <input type="radio" name="type" id="mobile-design" class="purpose-radio-input" value="Standard">
                                <label for="mobile-design" class="purpose-radio-label">
                    <span class="label-icon">
                      <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_mobile_design.svg')}}" alt="standard" class="label-icon-default">
                      <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_mobile_design_green.svg')}}" alt="standard" class="label-icon-active">
                    </span>
                                    <span class="label-text">Standard Campaign</span>
                                </label>
                            </div>
                            <div class="purpose-radio">
                                <input type="radio" name="type" id="web-design" class="purpose-radio-input" value="Newsletter">
                                <label for="web-design" class="purpose-radio-label">
                      <span class="label-icon">
                        <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_web_design.svg')}}" alt="newsletter" class="label-icon-default">
                        <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_web_design_green.svg')}}" alt="newsletter" class="label-icon-active">
                      </span>
                                    <span class="label-text">Newsletter</span>
                                </label>
                            </div>
                        </div>
                    </section>
                    <h3>Step 2 Title</h3>
                    <section>
                        <h5 class="bd-wizard-step-title">Step 2</h5>
                        <h2 class="section-heading">Enter Campaign Details</h2>

                        @include('inboxer::backend.campaigns.form')

                    </section>
                    <h3>Step 3 Title</h3>
                    <section>
                        <h5 class="bd-wizard-step-title">Step 3</h5>
                        <h2 class="section-heading">Recipients and Schedule</h2>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <?php
                                    $field_name = 'default_mail_list_id';
                                    $field_lable = __("inboxer::$module_name.Send to");
                                    $field_relation = "mailList";
                                    $field_placeholder = __("Select an option");
                                    $required = "required";
                                    ?>
                                    {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
                                    {{ html()->select($field_name, isset($$module_name_singular)?optional($$module_name_singular->$field_relation)->pluck('name', 'id'):'')->placeholder($field_placeholder)->class('form-control select2-mailinglist')->attributes(["$required"]) }}
                                </div>

                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <?php
                                    $field_name = 'template_id';
                                    $field_lable = __("inboxer::$module_name.Send what");
                                    $field_relation = "templates";
                                    $field_placeholder = __("Select an option");
                                    $required = "required";
                                    ?>
                                    {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
                                    {{ html()->select($field_name, isset($$module_name_singular)?optional($$module_name_singular->$field_relation)->pluck('name', 'id'):'')->placeholder($field_placeholder)->class('form-control select2-template')->attributes(["$required"]) }}
                                </div>

                            </div>
                        </div>
                        <div class="row">
    <div class="col-6">
        <div class="form-group">
            <?php
            $field_name = 'run_at';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            <div class="input-group date datetime" id="{{$field_name}}" data-target-input="nearest">
                {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control datetimepicker-input')->attributes(["$required", 'data-target'=>"#$field_name"]) }}
                <div class="input-group-append" data-target="#{{$field_name}}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

                    </section>
                    <h3>Step 4 Title</h3>
                    <section>
                        <h5 class="bd-wizard-step-title">Step 4</h5>
                        <h2 class="section-heading mb-5">Review and Enable</h2>
                        <h6 class="font-weight-bold">Campaign type</h6>
                        <p class="mb-4" id="campaign-type">Branding</p>
                        <h6 class="font-weight-bold">Campaign Details</h6>
                        <p class="mb-4"><span id="enteredFromName">SiteAdmin</span> <span id="enteredFromEmail">no-reply</span> <br>
                            Name: <span id="enteredCampaignName">Default Name</span> <br>
                            Subject: <span id="enteredCampaignSubject">Default Subject</span></p>
                        <h6 class="font-weight-bold">Sending List</h6>
                        <p class="mb-4" id="enteredListTo">Default</p>
                        <h6 class="font-weight-bold">Send At</h6>
                        <p class="mb-4" id="enteredSendWhen">NA</p>


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

                    </section>

                </div>

                {{--<div class="row">
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
                </div>--}}

                {{ html()->form()->close() }}

            </div>
        </div>
    </div>

    <div class="card-footer mt-3">
        <div class="row">
            <div class="col">

            </div>
        </div>
    </div>
</div>

@stop

@push('after-scripts')
    <script src="{{ asset('vendor/simplepleb/inboxer/plugins/bdwizard/js/jquery.steps.min.js') }}"></script>

    <script type="text/javascript">

        $("#wizard").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slide",
            stepsOrientation: "vertical",
            titleTemplate: '<span class="number">#index#</span>'
        });

        //Form control

        $('.purpose-radio-input').on('change', function(e) {
            $('#campaign-type').text(e.target.value);
        });
        $('#campaign_name').on('change', function(e) {
            $('#enteredCampaignName').text(e.target.value);
        });
        $('#default_subject').on('change', function(e) {
            $('#enteredCampaignSubject').text(e.target.value);
        });
        $('#from_name').on('change', function(e) {
            $('#enteredFromName').text(e.target.value);
        });
        $('#from_email').on('change', function(e) {
            $('#enteredFromEmail').text('< '+ e.target.value+' >');
        });
        $('#default_mail_list_id').on('change', function(e) {
            $('#enteredListTo').text( e.target.value);
        });
        $('#run_at').on('change', function(e) {
            $('#enteredSendWhen').text( e.target.value);
        });




        $(document).ready(function() {
            $('.select2-mailinglist').select2({
                theme: "bootstrap",
                placeholder: '@lang("Select an option")',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: '{{route("backend.lists.index_list")}}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
            $('.select2-template').select2({
                theme: "bootstrap",
                placeholder: '@lang("Select an option")',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: '{{route("backend.templates.index_list")}}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $('.datetime').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar-alt',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'fas fa-times'
                }
            });


        });



    </script>
@endpush
@push('before-styles')
    <!-- Select2 Bootstrap 4 Core UI -->
    <link href="{{ asset('vendor/simplepleb/inboxer/plugins/bdwizard/css/bd-wizard.css') }}" rel="stylesheet" />

@endpush
