<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdvertiseController extends Controller
{
    public function show(Advertise $advertise)
    {
        return Inertia::render('Advertise', ['entity' => $advertise]);
    }

    public function destroy(Advertise $advertise)
    {
        $advertise->delete();

        return redirect()->route('dashboard');
    }
}
