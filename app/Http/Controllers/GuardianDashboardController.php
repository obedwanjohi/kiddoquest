<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuardianDashboardController extends Controller
{
    public function index(): View
    {
        $guardian = Auth::guard('guardian')->user();
        $children = $guardian->children()->orderBy('created_at')->get();

        return view('guardian.dashboard', compact('guardian', 'children'));
    }
}