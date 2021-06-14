<?php

/**
 * Putting this here to help remind you where this came from.
 *
 * I'll get back to improving this and adding more as time permits
 * if you need some help feel free to drop me a line.
 *
 * * Twenty-Years Experience
 * * PHP, JavaScript, Laravel, MySQL, Java, Python and so many more!
 *
 *
 * @author  Simple-Pleb <plebeian.tribune@protonmail.com>
 * @website https://www.simple-pleb.com
 * @source https://github.com/simplepleb/article-module
 *
 * @license MIT For Premium Clients
 *
 * @since 1.0
 *
 */

namespace Modules\Inboxer\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Modules\Inboxer\Entities\Template;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

class TemplateController extends Controller
{
    use Authorizable;

    public function __construct() {
        // Page Title
        $this->module_title = 'Templates';

        // module name
        $this->module_name = 'templates';

        // directory path of the module
        $this->module_path = 'templates';

        // module icon
        $this->module_icon = 'fas fa-list';

        // module model name, path
        $this->module_model = "Modules\Inboxer\Entities\Template";
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'List';

        $$module_name = $module_model::paginate();

        return view(
            "inboxer::backend.templates.index_datatable",
            compact('module_title', 'module_name', "$module_name", 'module_icon', 'module_name_singular', 'module_action')
        );
    }

    public function index_data()
    {
        $module_name = $this->module_name;
        $module_model = $this->module_model;

        $$module_name = $module_model::select('id', 'uid', 'name', 'image', 'source', 'updated_at', 'customer_id');

        $data = $$module_name;

        return Datatables::of($$module_name)
                        ->addColumn('action', function ($data) {
                            $module_name = $this->module_name;

                            return view('inboxer::backend.templates.includes.action_column', compact('module_name', 'data'));
                        })
                        ->editColumn('image', function ($data) {
                            $img = '';
                            if( $data->image ){
                                $img = '
                                <div class="card panel-flat panel-template-style">
                                        <div class="card-body">
                                            <img src="/admin/templates/'.$data->uid.'/image?v=888" style="width: auto; height:120px;">
                                        </div>
                                    </div>
                                ';
                            }

                            return $img;
                        })
                        ->editColumn('updated_at', function ($data) {

                            $diff = Carbon::now()->diffInHours($data->updated_at);

                            if ($diff < 25) {
                                return $data->updated_at->diffForHumans();
                            } else {
                                return $data->updated_at->isoFormat('LLLL');
                            }
                        })
                        ->rawColumns(['image', 'name', 'source', 'action'])
                        ->orderColumns(['id'], '-:column $1')
                        ->make(true);
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @param Request $request
     * @return Response
     */
    public function index_list(Request $request)
    {
        $module_name = $this->module_name;
        $module_model = $this->module_model;

        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $query_data = $module_model::where('name', 'LIKE', "%$term%")->limit(10)->get();

        $$module_name = [];

        foreach ($query_data as $row) {
            ${$module_name}[] = [
                'id'   => $row->id,
                'text' => $row->name,
            ];
        }

        return response()->json($$module_name);
    }

    /**
     * Template screenshot.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function image(Request $request)
    {
        $template = Template::findByUid($request->uid);
        $file_path = storage_path('/');

        if (!empty($template->image) && file_exists($file_path.$template->image.'.thumb.jpg')) {
            $img = \Image::make($file_path.$template->image.'.thumb.jpg');
        } else {
            $img = \Image::make(public_path('/vendor/simplepleb/images/placeholder.jpg'));
        }

        return $img->response();
    }

    /**
     * Content of template.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function content(Request $request)
    {
        $template = Template::findByUid($request->uid);

        echo $template->content;
    }

    /**
     * Preview template.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request, $id)
    {
        $template = Template::findByUid($id);

        // Convert to inline css if template source is builder
        if ($template->source == 'builder') {
            $cssToInlineStyles = new CssToInlineStyles();
            $html = $template->content;
            $css = file_get_contents(public_path("vendor/simplepleb/inboxer/public/css/res_email.css"));

            // output
            $template->content = $cssToInlineStyles->convert(
                $html,
                $css
            );
        }

        return view('inboxer::backend.templates.preview', [
            'template' => $template,
        ]);
    }

    /**
     * Save template screenshot.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @return \Illuminate\Http\Response
     */
    public function saveImage(Request $request, $id)
    {
        $template = Template::findByUid($id);

        $upload_loca = 'email_templates/';
        $file_path = storage_path('/');
        $upload_path = $file_path.$upload_loca;
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $filename = 'screenshot-'.$id.'.png';

        // remove "data:image/png;base64,"
        $uri = substr($request->data, strpos($request->data, ',') + 1);

        // save to file
        file_put_contents($upload_path.$filename, base64_decode($uri));

        // create thumbnails
        $img = \Image::make($upload_path.$filename);
        $img->fit(178, 200)->save($upload_path.$filename.'.thumb.jpg');

        // save
        $template->image = $upload_loca.$filename;
        $template->save();
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;

        $module_action = 'Upload';

        // validate and save posted data
        if ($request->isMethod('post')) {
            $template = Template::upload($request);

            Flash::success("<i class='fas fa-check'></i> New '".Str::singular($module_title)."' Added")->important();
            return redirect()->action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@index');
        }

        return view('inboxer::backend.templates.upload', [
            'module_action' => $module_action,
            'module_title' => $module_title,
            'module_name' => $module_name,
            'module_icon' => $module_icon,
        ]);

    }
    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function build(Request $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        $module_action = 'Build';

        $template = new Template();
        $template->name = trans('inboxer::messages.untitled_template');

        $elements = [];
        if(isset($request->style)) {
            $elements = Template::templateStyles()[$request->style];
        }

        return view('inboxer::backend.templates.build', [
            'template' => $template,
            'elements' => $elements,
            'module_action' => $module_action,
            'module_title' => $module_title,
            'module_name' => $module_name,
            'module_icon' => $module_icon,
        ]);
    }

    /**
     * Select template style.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function buildSelect(Request $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;

        $module_action = 'Build';

        $template = new Template();

        return view('inboxer::backend.templates.build_start', [
            'template' => $template,
            'module_action' => $module_action,
            'module_title' => $module_title,
            'module_name' => $module_name,
            'module_icon' => $module_icon,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Create';
        $template = new Template();

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('inboxer::backend.templates.create', [
            'template' => $template,
            'module_action' => $module_action,
            'module_title' => $module_title,
            'module_name' => $module_name,
            'module_icon' => $module_icon,
        ]);

        // Log::info(label_case($module_title.' '.$module_action).' | User:'.Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return view(
            "inboxer::backend.templates.create",
            compact('module_title', 'module_name', 'module_icon', 'module_action', 'module_name_singular', 'categories')
        );
    }


    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function rebuild(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        /*if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }*/

        return view('inboxer::backend.templates.rebuild', [
            'template' => $template,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Store';
        $customer = $request->user()->customer;

        $template = new Template();
        $template->customer_id = $customer->id;

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            $this->validate($request, $rules);

            // Save template
            $template->fill($request->all());
            $template->source = 'editor';
            if(isset($request->source)) {
                $template->source = $request->source;
            }

            //// update content
            //$template->content = preg_replace('/href\=\'([^\']*\{)/',"href='{", $template->content);
            //$template->content = preg_replace('/href\=\"([^\"]*\{)/','href="{', $template->content);

            $template->save();

            Flash::success("<i class='fas fa-check'></i> New '".Str::singular($module_title)."' Added")->important();

            Log::info(label_case($module_title.' '.$module_action)." | '".$$module_name_singular->name.'(ID:'.$$module_name_singular->id.") ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');


            return redirect()->action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@index');
        }


// @todo Create Event
        // event(new PostCreated($$module_name_singular));


        return redirect("admin/$module_name");
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Show';

        $$module_name_singular = $module_model::findOrFail($id);

        $activities = Activity::where('subject_type', '=', $module_model)
                                ->where('log_name', '=', $module_name)
                                ->where('subject_id', '=', $id)
                                ->latest()
                                ->paginate();

        Log::info(label_case($module_title.' '.$module_action).' | User:'.Auth::user()->name.'(ID:'.Auth::user()->id.')');

        if( \Module::has('Comment'))
            $comment_active = true;
        if( \Module::has('Tag'))
            $comment_active = true;


        return view(
            "inboxer::backend.$module_name.show",
            compact('module_title', 'module_name', 'module_icon', 'module_name_singular', 'module_action', "$module_name_singular", 'activities')
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Edit';

        $$module_name_singular = $module_model::findOrFail($id);

        $categories = Category::pluck('name', 'id');

        Log::info(label_case($module_title.' '.$module_action)." | '".$$module_name_singular->name.'(ID:'.$$module_name_singular->id.") ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return view(
            "inboxer::backend.$module_name.edit",
            compact('categories', 'module_title', 'module_name', 'module_icon', 'module_name_singular', 'module_action', "$module_name_singular")
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Update';

        $template = Template::findByUid($request->uid);

        if ($request->isMethod('patch')) {
            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            $this->validate($request, $rules);

            // Save template
            $template->fill($request->all());

            //// update content
            //$template->content = preg_replace('/href\=\'([^\']*\{)/',"href='{", $template->content);
            //$template->content = preg_replace('/href\=\"([^\"]*\{)/','href="{', $template->content);

            $template->save();

            // @todo Add Event
            // event(new PostUpdated($$module_name_singular));

            Flash::success("<i class='fas fa-check'></i> '".Str::singular($module_title)."' Updated Successfully")->important();

            Log::info(label_case($module_title.' '.$module_action)." | '".$template->name.'(ID:'.$template->id.") ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

            return redirect()->action('\Modules\Inboxer\Http\Controllers\Backend\TemplateController@index');


        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'destroy';

        $$module_name_singular = $module_model::findOrFail($id);

        $$module_name_singular->delete();

        Flash::success('<i class="fas fa-check"></i> '.label_case($module_name_singular).' Deleted Successfully!')->important();

        Log::info(label_case($module_title.' '.$module_action)." | '".$$module_name_singular->name.', ID:'.$$module_name_singular->id." ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return redirect("admin/$module_name");
    }

    /**
     * List of trashed ertries
     * works if the softdelete is enabled.
     *
     * @return Response
     */
    public function trashed()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Trash List';

        $$module_name = $module_model::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        Log::info(label_case($module_title.' '.$module_action).' | User:'.Auth::user()->name);

        return view(
            "inboxer::backend.$module_name.trash",
            compact('module_title', 'module_name', "$module_name", 'module_icon', 'module_name_singular', 'module_action')
        );
    }

    /**
     * Restore a soft deleted entry.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function restore($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Restore';

        $$module_name_singular = $module_model::withTrashed()->find($id);
        $$module_name_singular->restore();

        Flash::success('<i class="fas fa-check"></i> '.label_case($module_name_singular).' Data Restoreded Successfully!')->important();

        Log::info(label_case($module_action)." '$module_name': '".$$module_name_singular->name.', ID:'.$$module_name_singular->id." ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return redirect("admin/$module_name");
    }
}
