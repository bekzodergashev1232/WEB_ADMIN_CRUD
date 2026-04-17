<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tuman;
use App\Models\User;
use App\Models\Viloyat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validated = $request->validate(
                [
                    'nick_name' => 'required|string',
                    'password'  => 'required|string|min:8',
                ],
                [
                    'nick_name.string' => 'Foydalanuvchi nomi matn bo\'lishi kerak.',                    'password.required'  => 'Parol kiritish majburiy.',
                    'password.string'    => 'Parol matn bo\'lishi kerak.',
                    'password.min'       => 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak.',
                ]
            );

            $user = User::where('nick_name', $validated['nick_name'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Nick name yoki parol noto\'g\'ri.',
                ], 401);
            }
            $viloyatName = $user->viloyat_id
                ? Viloyat::where('soato', $user->viloyat_id)->first()
                : null;

            $tumanName = $user->tuman_id
                ? Tuman::where('soato', $user->tuman_id)->first()
                : null;
            $roleName = Role::find($user->role_id);

            $token = $user->createToken('auth_token')->accessToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    ...$user->only(['id', 'name', 'email', 'phone', 'address']),
                    'viloyat_name' => $viloyatName?->name_uz,
                    'tuman_name'   => $tumanName?->name_uz,
                    'role_name'    => $roleName?->name,
                    'role_value'   => $roleName?->value,
                ],
            ], 200);
        } catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function register(Request $request)
    {
        try {

            $validated = $request->validate(
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8',
                    'phone' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'viloyat_id' => 'required|integer',
                    'tuman_id' => 'required|integer',
                    'nick_name' => 'required|string|unique:users'
                ],
                [
                    // name
                    'name.required' => 'Ism kiritish majburiy.',
                    'name.string' => 'Ism matn bo\'lishi kerak.',
                    'name.max' => 'Ism 255 ta belgidan oshmasligi kerak.',

                    // email
                    'email.required' => 'Email kiritish majburiy.',
                    'email.string' => 'Email matn bo\'lishi kerak.',
                    'email.email' => 'Email formati noto\'g\'ri.',
                    'email.max' => 'Email 255 ta belgidan oshmasligi kerak.',
                    'email.unique' => 'Bu email allaqachon ro\'yxatdan o\'tgan.',

                    // password
                    'password.required' => 'Parol kiritish majburiy.',
                    'password.string' => 'Parol matn bo\'lishi kerak.',
                    'password.min' => 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak.',

                    // phone
                    'phone.required' => 'Telefon raqam kiritish majburiy.',
                    'phone.string' => 'Telefon raqam matn bo\'lishi kerak.',
                    'phone.max' => 'Telefon raqam 255 ta belgidan oshmasligi kerak.',

                    // address
                    'address.required' => 'Manzil kiritish majburiy.',
                    'address.string' => 'Manzil matn bo\'lishi kerak.',
                    'address.max' => 'Manzil 255 ta belgidan oshmasligi kerak.',

                    // viloyat_id
                    'viloyat_id.required' => 'Viloyat tanlash majburiy.',
                    'viloyat_id.integer' => 'Viloyat ID butun son bo\'lishi kerak.',

                    // tuman_id
                    'tuman_id.required' => 'Tuman tanlash majburiy.',
                    'tuman_id.integer' => 'Tuman ID butun son bo\'lishi kerak.',

                    // nick_name
                    'nick_name.required' => 'Foydalanuvchi nomi kiritish majburiy.',
                    'nick_name.string' => 'Foydalanuvchi nomi matn bo\'lishi kerak.',
                    'nick_name.unique' => 'Bu foydalanuvchi nomi allaqachon band.',
                ]
            );

            $user = User::create(
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'viloyat_id' => $validated['viloyat_id'],
                    'tuman_id' => $validated['tuman_id'],
                    'nick_name' => $validated['nick_name'],
                    'role_id' => User::USER,
                ]
            );
            $token = $user->createToken('auth_token')->accessToken;

            $viloyatName = $user->viloyat_id
                ? Viloyat::where('soato', $user->viloyat_id)->first() : null;
            $tumanName = $user->tuman_id
                ? Tuman::where('soato', $user->tuman_id)->first() : null;
            $roleName = Role::find($user->role_id);

            return response()->json([
                'message' => 'Muvaffaqiyatli ro\'yxatdan o\'tdingiz.',
                'token' => $token,
                'user' => [
                    ...$user->only(['id', 'name', 'email', 'phone', 'address']),
                    'viloyat_name' => $viloyatName?->name_uz,
                    'tuman_name' => $tumanName?->name_uz,
                    'role_name' => $roleName?->name,
                    'role_value' => $roleName?->value,
                ],
            ], 201);
        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
