@extends('marketing::layouts.builder')

@section('title', trans('inboxer::messages.create_template'))

@section('content')

        <div class="left">
            <form action="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@store') }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}
                <input type="text" name="name" value="{{ $template->name }}" class="required" />
                <input type="hidden" name="source" value="builder" class="required" />
                <textarea class="hide template_content" name="content"></textarea>
                <button class="btn btn-primary mr-5">{{ trans('inboxer::messages.save') }}</button>
                <a href="{{ action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@index') }}" class="btn bg-slate">{{ trans('inboxer::messages.cancel') }}</a>
            </form>
        </div>
        <div class="right">

        </div>

        <script>
            $(document).ready(function() {

                @foreach($elements as $element)
                    insertElement("{{ $element }}");
                @endforeach

            });
        </script>

@endsection

@section('template_content')

    <content>
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('/vendor/simplepleb/marketing/public/css/res_email.css') }}" />
        <center class="wrapper">
            <div class="webkit">
                <!--[if (gte mso 9)|(IE)]>
                <table width="600" align="center">
                <tr>
                <td>
                <![endif]-->
                <table class="outer right-box" align="center">
                    <tr><td></td></tr>
                </table>
                <!--[if (gte mso 9)|(IE)]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </div>
        </center>
    </content>

@endsection
