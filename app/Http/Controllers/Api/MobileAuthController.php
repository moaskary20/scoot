<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    /**
     * Login with phone and password
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::where('phone', $request->phone)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
                ], 401);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حسابك غير مفعل. يرجى الانتظار حتى يتم تفعيله من قبل الإدارة',
                ], 403);
            }

            // Delete all existing tokens to prevent multiple device login
            // This ensures only one device can be logged in at a time
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'age' => $user->age,
                    'university_id' => $user->university_id,
                    'is_active' => $user->is_active,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الدخول',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:users,phone',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                // تاريخ الميلاد بصيغة YYYY/MM/DD
                'age' => 'required|date_format:Y/m/d',
                'university_id' => 'required|string',
                // صورة البطاقة الشخصية - وجه أمامي وخلفي
                'national_id_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'national_id_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // حساب السن من تاريخ الميلاد
            $birthDate = Carbon::createFromFormat('Y/m/d', $request->age);
            $calculatedAge = $birthDate->age;

            $userData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'age' => $calculatedAge,
                'university_id' => $request->university_id,
                'is_active' => $request->is_active ?? false, // Mobile accounts are inactive by default
            ];

            // Handle national ID photos upload (front & back)
            if ($request->hasFile('national_id_front')) {
                $front = $request->file('national_id_front');
                $frontName = time() . '_front_' . $front->getClientOriginalName();
                $front->storeAs('public/national_ids', $frontName);
                $userData['national_id_front_photo'] = 'national_ids/' . $frontName;
            }

            if ($request->hasFile('national_id_back')) {
                $back = $request->file('national_id_back');
                $backName = time() . '_back_' . $back->getClientOriginalName();
                $back->storeAs('public/national_ids', $backName);
                $userData['national_id_back_photo'] = 'national_ids/' . $backName;
            }

            $user = User::create($userData);

            // Track referral if referral code is provided
            if ($request->has('referral_code') && !empty($request->referral_code)) {
                \App\Http\Controllers\Api\MobileReferralController::trackReferral(
                    $user,
                    $request->referral_code
                );
            }

            // Create token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح. في انتظار التفعيل من قبل الإدارة',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'age' => $user->age,
                    'university_id' => $user->university_id,
                    'is_active' => $user->is_active,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء الحساب',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'age' => $user->age,
                    'university_id' => $user->university_id,
                    'national_id_photo' => $user->national_id_photo,
                    'avatar' => $user->avatar,
                    'is_active' => $user->is_active,
                    'wallet_balance' => $user->wallet_balance ?? 0,
                    'loyalty_points' => $user->loyalty_points ?? 0,
                    'loyalty_level' => $user->loyalty_level ?? 'bronze',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات المستخدم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الخروج',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة',
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث كلمة المرور بنجاح',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث كلمة المرور',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات غير صحيحة',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            // Upload new avatar
            $avatar = $request->file('avatar');
            $avatarName = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public/avatars', $avatarName);
            $avatarPath = 'avatars/' . $avatarName;

            // Update user
            $user->update(['avatar' => $avatarPath]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الصورة الشخصية بنجاح',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'age' => $user->age,
                    'university_id' => $user->university_id,
                    'national_id_photo' => $user->national_id_photo,
                    'avatar' => $user->avatar,
                    'is_active' => $user->is_active,
                    'wallet_balance' => $user->wallet_balance ?? 0,
                    'loyalty_points' => $user->loyalty_points ?? 0,
                    'loyalty_level' => $user->loyalty_level ?? 'bronze',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث الصورة الشخصية',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

