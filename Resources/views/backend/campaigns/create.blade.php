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
                                <input type="radio" name="purpose" id="branding" class="purpose-radio-input" value="Welcome" checked>
                                <label for="branding" class="purpose-radio-label">
                   <span class="label-icon">
                     <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_branding.svg')}}" alt="welcome" class="label-icon-default">
                     <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_branding_green.svg')}}" alt="welcome" class="label-icon-active">
                   </span>
                                    <span class="label-text">Welcome Series</span>
                                </label>
                            </div>
                            <div class="purpose-radio">
                                <input type="radio" name="purpose" id="mobile-design" class="purpose-radio-input" value="Standard">
                                <label for="mobile-design" class="purpose-radio-label">
                    <span class="label-icon">
                      <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_mobile_design.svg')}}" alt="standard" class="label-icon-default">
                      <img src="{{asset('vendor/simplepleb/inboxer/plugins/bdwizard/images/icon_mobile_design_green.svg')}}" alt="standard" class="label-icon-active">
                    </span>
                                    <span class="label-text">Standard Campaign</span>
                                </label>
                            </div>
                            <div class="purpose-radio">
                                <input type="radio" name="purpose" id="web-design" class="purpose-radio-input" value="Newsletter">
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

                        Recipients and Schedule

                    </section>
                    <h3>Step 4 Title</h3>
                    <section>
                        <h5 class="bd-wizard-step-title">Step 4</h5>
                        <h2 class="section-heading mb-5">Review and Enable</h2>
                        <h6 class="font-weight-bold">Campaign type</h6>
                        <p class="mb-4" id="campaign-type">Branding</p>
                        <h6 class="font-weight-bold">Campaign Details</h6>
                        <p class="mb-4"><span id="enteredFirstName">Cha</span> <span id="enteredLastName">Ji-Hun C</span> <br>
                            Phone: <span id="enteredPhoneNumber">+230-582-6609</span> <br>
                            Email: <span id="enteredEmailAddress">willms_abby@gmail.com</span></p>

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



    </script>
@endpush
@push('before-styles')
    <!-- Select2 Bootstrap 4 Core UI -->
    <link href="{{ asset('vendor/simplepleb/inboxer/plugins/bdwizard/css/bd-wizard.css') }}" rel="stylesheet" />

@endpush
