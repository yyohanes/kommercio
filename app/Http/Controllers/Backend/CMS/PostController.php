<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\CMS\PostFormRequest;
use Kommercio\Models\CMS\Post;
use Kommercio\Models\CMS\PostCategory;
use Illuminate\Support\Facades\DB;

class PostController extends Controller{
    public function index(Request $request)
    {
        $qb = Post::query();

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            //Join Translation and Detail
            $qb->with('postCategories')->joinTranslation()->selectSelf();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(trim($search) != ''){
                    if($searchKey == 'post_category'){
                        $qb->whereHas('postCategories', function($query) use ($search){
                            $query->whereTranslationLike('name', '%'.$search.'%');
                        });
                    }elseif($searchKey == 'name') {
                        $qb->whereTranslationLike('name', '%'.$search.'%');
                    }else{
                        $qb->where($searchKey, 'LIKE', '%'.$search.'%');
                    }
                }
            }

            $filteredRecords = $qb->count();

            $columns = $request->input('columns');
            foreach($request->input('order', []) as $order){
                $orderColumn = $columns[$order['column']];

                $qb->orderBy($orderColumn['name'], $order['dir']);
            }

            $qb->orderBy('posts.created_at', 'DESC');

            if($request->has('length')){
                $qb->take($request->input('length'));
            }

            if($request->has('start') && $request->input('start') > 0){
                $qb->skip($request->input('start'));
            }

            $posts = $qb->get();

            $meat = $this->prepareDatatables($posts, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        return view('backend.cms.post.index');
    }

    protected function prepareDatatables($posts, $orderingStart=0)
    {
        $meat= [];

        foreach($posts as $idx=>$post){
            $action = FormFacade::open(['route' => ['backend.cms.post.delete', 'id' => $post->id]]);
            $action .= '<div class="btn-group btn-group-sm">';

            if(Gate::allows('access', ['edit_post'])):
                $action .= '<a class="btn btn-default" href="'.route('backend.cms.post.edit', ['id' => $post->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i> Edit</a>';
            endif;

            if(Gate::allows('access', ['delete_post'])):
                $action .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>';
            endif;

            $action .= '</div>'.FormFacade::close();

            $meat[] = [
                $idx + 1 + $orderingStart,
                $post->name.' (ID: '.$post->id.')',
                implode(',', $post->postCategories->pluck('name')->all()),
                $post->created_at?$post->created_at->format('d M Y H:i'):null,
                '<i class="fa fa-'.($post->active?'check text-success':'remove text-danger').'"></i>',
                $action
            ];
        }

        return $meat;
    }

    public function create(Request $request)
    {
        $post = new Post();

        return view('backend.cms.post.create', [
            'post' => $post,
        ]);
    }

    public function store(PostFormRequest $request)
    {
        $post = new Post();
        $post->fill($request->all());
        $post->save();

        $post->postCategories()->sync($request->input('categories', []));

        if($request->has('image')){
            foreach($request->input('image', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('image_caption.'.$idx, null),
                    'locale' => $post->getTranslation()->locale
                ];
            }
            $post->getTranslation()->attachMedia($images, 'image');
        }

        if($request->has('thumbnail')){
            foreach($request->input('thumbnail', []) as $idx=>$image){
                $thumbnail[$image] = [
                    'type' => 'thumbnail',
                    'caption' => $request->input('thumbnail_caption.'.$idx, null),
                    'locale' => $post->getTranslation()->locale
                ];
            }
            $post->getTranslation()->attachMedia($thumbnail, 'thumbnail');
        }

        return redirect($request->get('backUrl', route('backend.cms.post.index')))->with('success', [$post->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);

        return view('backend.cms.post.edit', [
            'post' => $post,
        ]);
    }

    public function update(PostFormRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->fill($request->all());
        $post->save();

        $post->postCategories()->sync($request->input('categories', []));

        $images = [];
        foreach($request->input('image', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('image_caption.'.$idx, null),
                'locale' => $post->getTranslation()->locale
            ];
        }
        $post->getTranslation()->syncMedia($images, 'image');

        $thumbnail = [];
        foreach($request->input('thumbnail', []) as $idx=>$image){
            $thumbnail[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnail_caption.'.$idx, null),
                'locale' => $post->getTranslation()->locale
            ];
        }
        $post->getTranslation()->syncMedia($thumbnail, 'thumbnail');

        return redirect($request->get('backUrl', route('backend.cms.post.index')))->with('success', [$post->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);

        $name = $post->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($post->translations as $translation){
            $translation->deleteMedia('image');
            $translation->deleteMedia('thumbnail');
        }

        $post->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}