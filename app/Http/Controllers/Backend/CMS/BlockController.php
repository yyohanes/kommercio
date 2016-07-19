<?php

namespace Kommercio\Http\Controllers\Backend\CMS;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\CMS\BlockFormRequest;
use Kommercio\Models\CMS\Block;

class BlockController extends Controller{
    public function index()
    {
        $qb = Block::orderBy('created_at', 'DESC');

        $blocks = $qb->get();

        return view('backend.cms.block.index', [
            'blocks' => $blocks,
        ]);
    }

    public function create()
    {
        $block = new Block();

        return view('backend.cms.block.create', [
            'block' => $block,
        ]);
    }

    public function store(BlockFormRequest $request)
    {
        $block = new Block();

        $block->fill($request->all());
        $block->save();

        return redirect()->route('backend.cms.block.index')->with('success', [$block->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $block = Block::findOrFail($id);

        return view('backend.cms.block.edit', [
            'block' => $block,
        ]);
    }

    public function update(BlockFormRequest $request, $id)
    {
        $block = Block::findOrFail($id);

        $block->fill($request->all());
        $block->save();

        return redirect($request->get('backUrl', route('backend.cms.block.index')))->with('success', [$block->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $block = Block::findOrFail($id);

        $name = $block->name;

        $block->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}