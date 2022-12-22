<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdvertiseController extends Controller
{
    public function show(Advertise $advertise)
    {
        $advertise->setAttribute('is_favorite', UserFavorite::query()->where('advertise_id', $advertise->id)->where('user_id', auth()->user()->id)->exists());
        return Inertia::render('Advertise', ['entity' => $advertise]);
    }

    public function destroy(Advertise $advertise)
    {
        $advertise->delete();

        return redirect()->route('dashboard');
    }

    public function favorite(Advertise $advertise)
    {
        if ($userFavorite = UserFavorite::query()->where('advertise_id', $advertise->id)->where('user_id', auth()->user()->id)->first()) {
            $userFavorite->delete();

            return back();
        }

        UserFavorite::query()
            ->create([
                'advertise_id' => $advertise->id,
                'user_id' => auth()->user()->id,
            ]);

        return back();

    }
}
