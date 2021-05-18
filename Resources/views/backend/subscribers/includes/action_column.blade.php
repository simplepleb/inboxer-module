<div class="float-right">
    {{--<x-buttons.create route='{{ route("backend.subscribers.create",$list->uid ) }}' title="{{__('Create')}} {{ ucwords(Str::singular($module_name)) }}"/>--}}
    <a href="#" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i> </a>
    <a href="#" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Add to blacklist"><i class="fas fa-ban"></i> </a>
    <div class="btn-group" role="group" aria-label="Toolbar button groups">
        <div class="btn-group-sm" role="group">
            <button id="btnGroupToolbar" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-cog"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupToolbar">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-eye"></i> View history
                </a>
            </div>
        </div>
    </div>
</div>
