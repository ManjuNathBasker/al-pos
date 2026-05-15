<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Models\TableSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index()
    {
        $tables = RestaurantTable::with('section')->get();
        $sections = TableSection::all();
        return view('restaurant.tables.index', compact('tables', 'sections'));
    }

    public function map()
    {
        $sections = TableSection::with(['tables' => function($q) {
            $q->with('activeOrder');
        }])->get();

        $nonDineInOrders = \App\Models\Order::whereIn('service_type', ['takeaway', 'delivery'])
            ->where('created_at', '>=', now()->startOfDay())
            ->whereIn('kitchen_status', ['pending', 'preparing', 'ready'])
            ->get();
        
        return view('restaurant.tables.map', compact('sections', 'nonDineInOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:table_sections,id',
            'capacity' => 'required|integer|min:1',
        ]);

        $data = $request->all();
        $data['qr_token'] = Str::random(32);

        RestaurantTable::create($data);

        return redirect()->back()->with('success', 'Table created successfully.');
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:table_sections,id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved,cleaning',
        ]);

        $table->update($request->all());

        return redirect()->back()->with('success', 'Table updated successfully.');
    }

    public function destroy(RestaurantTable $table)
    {
        $table->delete();
        return redirect()->back()->with('success', 'Table deleted successfully.');
    }
}
