<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {

        $user = Auth()->user();

        $categories = [];

        foreach (['art', 'geography', 'history', 'science', 'sports'] as $category) {
            [$correct, $total] = explode('/', $user->{$category});
            $correct = (int) $correct;
            $total = (int) $total;

            $categories[$category] = [
                'label' => ucfirst($category),
                'correct' => $correct,
                'total' => $total,
                'percentage' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
            ];
        }

        return view('profile', ['categories' => $categories]);
    }
}
