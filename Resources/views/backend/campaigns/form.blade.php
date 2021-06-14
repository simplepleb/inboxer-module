<div class="row">
    <div class="col">
        <div class="form-group">
            <?php
            $field_name = 'name';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "required";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <?php
            $field_name = 'from_name';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>

    <div class="col-4">
        <div class="form-group">
            <?php
            $field_name = 'from_email';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = "Use Specific Identity";
            $required = "";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="form-group">
            <?php
            $field_name = 'subject';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "required";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <?php
            $field_name = 'reply_to';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "required";
            ?>
            {{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}
            {{ html()->text($field_name)->placeholder($field_placeholder)->class('form-control')->attributes(["$required"]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="form-check form-check-inline">
            <?php
            $checked = false;
            /*if( $field_value === '1') {
                $checked = true;
            }*/
            $field_name = 'track_opens';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "required";
            ?>
                {{ html()->checkbox($field_name)->class('form-check-input')->attributes([ 'data-toggle="toggle" data-width="200" data-on="Track Opens" data-off="Don\'t Track Opens"  data-onstyle="success" data-offstyle="warning"'])->checked($checked) }}
                {{--{{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}--}}
               {{-- @if($custom->field_help)--}}
                    {{--<small id="{{$field_name}}Help" class="form-text text-muted">{{ __("inboxer::$module_name.$field_name") }}</small>--}}
                {{--@endif--}}

        </div>
    </div>
     <div class="col-6">

        <div class="form-check form-check-inline">
            <?php
            $checked = false;
            /*if( $field_value === '1') {
                $checked = true;
            }*/
            $field_name = 'track_clicks';
            $field_lable = __("inboxer::$module_name.$field_name");
            $field_placeholder = $field_lable;
            $required = "required";
            ?>
                {{ html()->checkbox($field_name)->class('form-check-input')->attributes([ 'data-toggle="toggle"  data-width="200" data-on="Track Clicks" data-off="Don\'t Track Clicks" data-onstyle="success" data-offstyle="warning"'])->checked($checked) }}
                {{--{{ html()->label($field_lable, $field_name) }} {!! fielf_required($required) !!}--}}
                {{--@if($custom->field_help)--}}
                    {{--<small id="{{$field_name}}Help" class="form-text text-muted">{{ __("inboxer::$module_name.$field_name") }}</small>--}}
                {{--@endif--}}
        </div>
    </div>

</div>


<div></div>


<!-- Select2 Library -->
<x-library.select2 />
<x-library.datetime-picker />

@push('after-styles')
<!-- File Manager -->
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

@endpush

@push ('after-scripts')

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<!-- Date Time Picker & Moment Js-->
<script type="text/javascript">
$(function() {
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
