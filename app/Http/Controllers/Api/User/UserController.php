<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request){
        $authUserIds = auth()->id();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'string|min:8',
            'phone' => 'required|string|max:255',
            'nick_name' => 'required|string|unique:users',
            'address' => 'required|string',
            'viloyat_id' => 'required|integer',
            'tuman_id' => 'required|integer',
            'role_id' => 'required|integer',
        ]);

        $userCreate = User::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone' => $validated['phone'],
                'nick_name' => $validated['nick_name'],
                'address' => $validated['address'],
                'viloyat_id' => $validated['viloyat_id'],
                'tuman_id' => $validated['tuman_id'],
                'role_id' => $validated['role_id'],
                'created_by' => $authUserIds,
            ]
        );
        $token = $userCreate->createToken('auth_token')->accessToken;
        return response()->json(['token' => $token], 201);

    }
}
