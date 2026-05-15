<?php

namespace App\Http\Controllers;

use App\Models\TableSection;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $sections = TableSection::withCount('tables')->get();
        return view('restaurant.sections.index', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        TableSection::create($request->all());
        return redirect()->back()->with('success', 'Section created successfully.');
    }

    public function update(Request $request, TableSection $section)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $section->update($request->all());
        return redirect()->back()->with('success', 'Section updated successfully.');
    }

    public function destroy(TableSection $section)
    {
        $section->delete();
        return redirect()->back()->with('success', 'Section deleted successfully.');
    }
}
