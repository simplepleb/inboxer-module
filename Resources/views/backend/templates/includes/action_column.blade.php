<div class="text-right">
    @can('edit_'.$module_name)
    <x-buttons.edit route='{!!route("backend.templates.rebuild", $data->uid)!!}' title="{{__('Edit')}} {{ ucwords(Str::singular($module_name)) }}" small="true" />
    @endcan

    <a class="btn btn-sm btn-primary" href="#preview" onclick='popupwindow("/admin/templates/{{ $data->uid }}/preview", "{{ $data->name }}", 800, 800)'><i class="fa fa-eye"></i> </a>
</div>
