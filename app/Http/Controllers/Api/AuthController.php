<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::withoutGlobalScopes()->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('هذا الحساب معطل، يرجى التواصل مع الإدارة', 403);
        }

        $token = $user->createToken('fieldops-token')->plainTextToken;

        $user->load(['tenant', 'roles']);

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
                'tenant' => $user->tenant,
            ],
        ], 'تم تسجيل الدخول بنجاح');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'تم تسجيل الخروج بنجاح');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['tenant', 'roles']);

        return ApiResponse::success([
            'id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames(),
            'tenant' => $user->tenant,
        ], 'بيانات الحساب');
    }
}
