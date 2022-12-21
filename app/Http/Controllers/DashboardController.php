<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use App\Models\Target;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $advertises = Advertise::query()
            ->when($request->get('room_count'), function ($query) use ($request) {
                $query->whereIn('room_count', $request->get('room_count'));
            })->when($request->get('price_min'), function ($query) use ($request) {
                $query->where('price', '>=', $request->get('price_min'));
            })->when($request->get('price_max'), function ($query) use ($request) {
                $query->where('price', '<=', $request->get('price_max'));
            })->when($request->get('address'), function ($query) use ($request) {
                $query->where('address', 'LIKE', '%' . $request->get('address') . '%');
            })->when($request->get('area_m_min'), function ($query) use ($request) {
                $query->where('area', '>=', $request->get('area_m_min'));
            })->when($request->get('area_m_max'), function ($query) use ($request) {
                $query->where('area', '<=', $request->get('area_m_max'));
            })->when($request->get('area_sot_max'), function ($query) use ($request) {
                $query->where('area', '<=', $request->get('area_sot_max'));
            })->when($request->get('area_sot_min'), function ($query) use ($request) {
                $query->where('area', '>=', $request->get('area_sot_min'));
            })
            ->latest();

        if ($request->get('search')) {
            $advertises = $advertises
                ->selectRaw('advertises.*,MATCH(name,description,address) AGAINST (? IN NATURAL LANGUAGE MODE) AS score', [$request->get('search')])
                ->orderByDesc('score')
                ->having('score', '>', '0');
        }


        $advertises = $advertises
            ->paginate(10);


        $advertises->links = $advertises->onEachSide(1)->links();

        return Inertia::render('Dashboard', compact('advertises'));
    }

    public function advertise(Advertise $advertise)
    {

    }
}
