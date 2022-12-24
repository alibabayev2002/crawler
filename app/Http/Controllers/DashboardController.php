<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use App\Models\District;
use App\Models\Target;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $advertises = Advertise::query()
            ->whereNotNull('room_count')
            ->with('districtTable')
            ->when($request->get('room_count'), function ($query) use ($request) {
                $query->whereIn('room_count', $request->get('room_count'));
            })->when($request->get('district'), function ($query) use ($request) {
                $query->where('district', $request->get('district'));
            })->when($request->get('category'), function ($query) use ($request) {
                $query->where('category', $request->get('category'));
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
            })->when($request->get('repair') && $request->get('repair') != 'all', function ($query) use ($request) {
                $query->where('repair', $request->get('repair'));
            })->when($request->get('document_type') && $request->get('document_type') != 'all', function ($query) use ($request) {
                $query->where('document_type', $request->get('document_type'));
            })
            ->latest();

        if ($request->get('search')) {
            $search = $request->get('search');

            $advertises = $advertises
                ->selectRaw('advertises.*,MATCH(name,description,address) AGAINST (? IN BOOLEAN MODE) AS score', [$search])
                ->orderByDesc('score')
                ->having('score', '>', '0');
        }


        $advertises = $advertises
            ->paginate(10);


        $advertises->links = $advertises->onEachSide(1)->appends($request->all())->links();

        $districts = District::selectRaw('id as value,name as label')->get();

        $categories = Advertise::query()
            ->selectRaw('DISTINCT(category)')
            ->pluck('category')->map(function ($item) {
                return ['label' => mb_strtoupper(substr($item, 0, 1)) . substr($item, 1), 'value' => $item];
            });

        return Inertia::render('Dashboard', compact('advertises', 'districts', 'categories'));
    }

    public function favorites(Request $request)
    {
        $ids = UserFavorite::query()
            ->where('user_id', auth()->user()->id)
            ->pluck('advertise_id')
            ->toArray();

        $advertises = Advertise::query()
            ->whereNotNull('room_count')
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
            })->when($request->get('repair') && $request->get('repair') != 'all', function ($query) use ($request) {
                $query->where('repair', $request->get('repair'));
            })->when($request->get('document_type') && $request->get('document_type') != 'all', function ($query) use ($request) {
                $query->where('document_type', $request->get('document_type'));
            })->whereIn('id', $ids)
            ->latest();

        if ($request->get('search')) {

            $search = $request->get('search');

            $search .= ' ' . str_replace(['ə'], ['e'], $request->get('search'));
            $search .= ' ' . str_replace(['ı'], ['i'], $request->get('search'));
            $search .= ' ' . str_replace(['ş'], ['s'], $request->get('search'));
            $search .= ' ' . str_replace(['ş'], ['sh'], $request->get('search'));
            $search .= ' ' . str_replace(['i'], ['ı'], $request->get('search'));
            $search .= ' ' . str_replace(['s'], ['ş'], $request->get('search'));
            $search .= ' ' . str_replace(['e'], ['ə'], $request->get('search'));


            $advertises = $advertises
                ->selectRaw('advertises.*,MATCH(name,description,address) AGAINST (? IN NATURAL LANGUAGE MODE) AS score', [$search])
                ->orderByDesc('score')
                ->having('score', '>', '0');
        }


        $advertises = $advertises
            ->paginate(10);


        $advertises->links = $advertises->onEachSide(1)->links();

        return Inertia::render('Dashboard', compact('advertises'));
    }
}
