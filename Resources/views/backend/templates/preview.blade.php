<script type="text/javascript" src="{{ URL::asset('/vendor/simplepleb/marketing/assets/js/core/libraries/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('/vendor/simplepleb/marketing/public/js/html2canvas/html2canvas.js') }}"></script>

<script>
    function captureFullPage()
    {
        html2canvas(document.body, {
            onrendered: function(canvas)
            {
                var img = canvas.toDataURL()
                $(".saving").show();
                $.post('{{ action('\Modules\Marketing\Http\Controllers\Backend\TemplateController@saveImage', $template->uid) }}', {data: img, '_token': '{!! csrf_token() !!}'}, function (file) {
                    $(".saving").hide();
                    if (opener) {
                        opener.tableFilterAll();
                    }
                });
            }
        });
    }
</script>

<div class="saving" style="display:none; position: fixed;
    height: 100%;
    vertical-align: middle;
    text-align: center;
    padding: 100px 0;
    font-size: 20px;
    color: #fff;
    width: 100%;
    background: rgba(0,0,0,0.7);">{{ trans('marketing::messages.saving_screenshot') }}</div>

{!! $template->content !!}

<script>
    setTimeout('captureFullPage()', 1000);
</script>
