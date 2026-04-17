<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(){
        $roles = Role::select([
            'id',
            'name',
            'value',
        ])->get();
        return response()->json($roles);
    }

    public function show($id){
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function store(Request $request){
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'value' => 'required|integer',
            ],
            [
                'name.required'  => 'Rol nomi kiritish majburiy.',
                'name.string'    => 'Rol nomi matn bo\'lishi kerak.',
                'name.unique'    => 'Bu rol nomi allaqachon mavjud.',
                'value.required' => 'Rol qiymati kiritish majburiy.',
                'value.integer'  => 'Rol qiymati butun son bo\'lishi kerak.',
                'value.unique'   => 'Bu rol qiymati allaqachon mavjud.',
            ]
        );

        $role = Role::create([
            'name' => $validated['name'],
            'value' => $validated['value'],
        ]);
        return response()->json([
            'message' => 'Rol muvaffaqiyatli yaratildi.',
            'role' => $role,
        ]);
    }

    public function destroy($id){
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json(['message' => 'Rol topilmadi.'], 404);
            }
            $role->update(['deleted_by' => auth()->id()]);
            $role->delete();
            return response()->json(['message' => 'Rol muvaffaqiyatli o\'chirildi.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
