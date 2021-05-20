<div class="text-right">
    @can('edit_'.$module_name)
    <x-buttons.edit route='{!!route("backend.$module_name.edit", $data)!!}' title="{{__('Edit')}} {{ ucwords(Str::singular($module_name)) }}" small="true" />
    @endcan
    {{--<x-buttons.show route='{!!route("backend.$module_name.show", $data)!!}' title="{{__('Show')}} {{ ucwords(Str::singular($module_name)) }}" small="true" />
    <a href='{!!route("backend.$module_name.subscribers", $data->uid)!!}' class="btn btn-success btn-sm ml-1" data-toggle="tooltip" title="Show Subscribers"><i class="fas fa-house-user"></i>&nbsp; Subscribers</a>--}}
    {{--<x-buttons.show route='{!!route("backend.$module_name.subscribers", $data->uid)!!}' title="{{__('Subscribers')}}" small="true" />--}}

</div>
