<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

            // Allow login even if account is not active
            // Account status will be checked when starting a trip

            // Delete all existing tokens to prevent multiple device login
            // This ensures only one device can be logged in at a time
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Determine account status
            $accountStatus = 'pending';
            if ($user->is_active) {
                $accountStatus = 'active';
            } elseif ($user->review_notes && !empty(trim($user->review_notes))) {
                $accountStatus = 'rejected';
            }

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
                    'review_notes' => $user->review_notes,
                    'account_status' => $accountStatus,
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

                'age' => 'required|date_format:Y/m/d',
                'university_id' => 'required|string',

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

            $birthDate = Carbon::createFromFormat('Y/m/d', $request->age);
            $calculatedAge = $birthDate->age;

            $userData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'age' => $calculatedAge,
                'university_id' => $request->university_id,
                'is_active' => false, // Mobile accounts are always inactive by default
            ];

            // Handle national ID photos upload (front & back)
            if ($request->hasFile('national_id_front')) {
                $front = $request->file('national_id_front');
                // Generate unique filename with extension
                $extension = $front->getClientOriginalExtension() ?: 'jpg';
                $frontName = time() . '_front_' . uniqid() . '.' . $extension;

                // Ensure directory exists
                $directory = 'national_ids';
                $fullPath = storage_path('app/public/' . $directory);
                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }

                // Store file using Storage facade
                $storedPath = Storage::disk('public')->putFileAs(
                    $directory,
                    $front,
                    $frontName
                );

                if ($storedPath) {
                    $userData['national_id_front_photo'] = $storedPath;
                    \Log::info('✅ National ID front photo saved', [
                        'stored_path' => $storedPath,
                        'filename' => $frontName,
                        'full_path' => storage_path('app/public/' . $storedPath),
                        'file_exists' => Storage::disk('public')->exists($storedPath),
                    ]);
                } else {
                    \Log::error('❌ Failed to save national ID front photo', [
                        'filename' => $frontName,
                        'directory' => $directory,
                    ]);
                }
            }

            if ($request->hasFile('national_id_back')) {
                $back = $request->file('national_id_back');
                // Generate unique filename with extension
                $extension = $back->getClientOriginalExtension() ?: 'jpg';
                $backName = time() . '_back_' . uniqid() . '.' . $extension;

                // Ensure directory exists
                $directory = 'national_ids';
                $fullPath = storage_path('app/public/' . $directory);
                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }

                // Store file using Storage facade
                $storedPath = Storage::disk('public')->putFileAs(
                    $directory,
                    $back,
                    $backName
                );

                if ($storedPath) {
                    $userData['national_id_back_photo'] = $storedPath;
                    \Log::info('✅ National ID back photo saved', [
                        'stored_path' => $storedPath,
                        'filename' => $backName,
                        'full_path' => storage_path('app/public/' . $storedPath),
                        'file_exists' => Storage::disk('public')->exists($storedPath),
                    ]);
                } else {
                    \Log::error('❌ Failed to save national ID back photo', [
                        'filename' => $backName,
                        'directory' => $directory,
                    ]);
                }
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
                    'review_notes' => $user->review_notes,
                    'account_status' => $user->is_active ? 'active' : ($user->review_notes && !empty(trim($user->review_notes)) ? 'rejected' : 'pending'),
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return more detailed error message
            $errorMessage = 'حدث خطأ في إنشاء الحساب';
            if (str_contains($e->getMessage(), 'SQLSTATE')) {
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    if (str_contains($e->getMessage(), 'email')) {
                        $errorMessage = 'البريد الإلكتروني مستخدم بالفعل';
                    } elseif (str_contains($e->getMessage(), 'phone')) {
                        $errorMessage = 'رقم الهاتف مستخدم بالفعل';
                    }
                } else {
                    $errorMessage = 'خطأ في قاعدة البيانات. يرجى المحاولة لاحقاً';
                }
            } elseif (str_contains($e->getMessage(), 'storage') || str_contains($e->getMessage(), 'file')) {
                $errorMessage = 'خطأ في رفع الصور. يرجى المحاولة مرة أخرى';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
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

            \Log::info('📱 User API Request', [
                'user_id' => $user->id,
                'age' => $user->age,
                'university_id' => $user->university_id,
                'age_type' => gettype($user->age),
                'university_id_type' => gettype($user->university_id),
            ]);

            // Determine account status
            $accountStatus = 'pending';
            if ($user->is_active) {
                $accountStatus = 'active';
            } elseif ($user->review_notes && !empty(trim($user->review_notes))) {
                $accountStatus = 'rejected';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'age' => $user->age, // Ensure it's sent as integer or null
                    'university_id' => $user->university_id, // Ensure it's sent as string or null
                    'national_id_photo' => $user->national_id_photo,
                    'avatar' => $user->avatar,
                    'is_active' => $user->is_active,
                    'review_notes' => $user->review_notes,
                    'account_status' => $accountStatus,
                    'wallet_balance' => $user->wallet_balance ?? 0,
                    'loyalty_points' => $user->loyalty_points ?? 0,
                    'loyalty_level' => $user->loyalty_level ?? 'bronze',
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('❌ Error in user API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            // Generate unique filename with extension
            $extension = $avatar->getClientOriginalExtension() ?: 'jpg';
            $avatarName = 'avatar_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $extension;

            // Ensure directory exists
            $directory = 'avatars';
            $fullPath = storage_path('app/public/' . $directory);
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            // Store file using Storage facade
            $storedPath = Storage::disk('public')->putFileAs(
                $directory,
                $avatar,
                $avatarName
            );

            if (!$storedPath) {
                \Log::error('❌ Failed to save avatar', [
                    'filename' => $avatarName,
                    'directory' => $directory,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في حفظ الصورة الشخصية',
                ], 500);
            }

            \Log::info('✅ Avatar saved', [
                'stored_path' => $storedPath,
                'filename' => $avatarName,
                'full_path' => storage_path('app/public/' . $storedPath),
                'file_exists' => Storage::disk('public')->exists($storedPath),
            ]);

            // Update user with stored path
            $user->refresh(); // Refresh to get latest data
            $user->update(['avatar' => $storedPath]);
            $user->refresh(); // Refresh again to get updated avatar path

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

    /**
     * Resubmit national ID photos (for rejected accounts)
     */
    public function resubmitNationalId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
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

            $user = $request->user();

            // Handle national ID photos upload (front & back)
            if ($request->hasFile('national_id_front')) {
                $front = $request->file('national_id_front');
                $extension = $front->getClientOriginalExtension() ?: 'jpg';
                $frontName = time() . '_front_' . uniqid() . '.' . $extension;

                $directory = 'national_ids';
                $fullPath = storage_path('app/public/' . $directory);
                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }

                $storedPath = Storage::disk('public')->putFileAs(
                    $directory,
                    $front,
                    $frontName
                );

                if ($storedPath) {
                    $user->national_id_front_photo = $storedPath;
                    \Log::info('✅ National ID front photo resubmitted', [
                        'stored_path' => $storedPath,
                        'filename' => $frontName,
                    ]);
                }
            }

            if ($request->hasFile('national_id_back')) {
                $back = $request->file('national_id_back');
                $extension = $back->getClientOriginalExtension() ?: 'jpg';
                $backName = time() . '_back_' . uniqid() . '.' . $extension;

                $directory = 'national_ids';
                $fullPath = storage_path('app/public/' . $directory);
                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }

                $storedPath = Storage::disk('public')->putFileAs(
                    $directory,
                    $back,
                    $backName
                );

                if ($storedPath) {
                    $user->national_id_back_photo = $storedPath;
                    \Log::info('✅ National ID back photo resubmitted', [
                        'stored_path' => $storedPath,
                        'filename' => $backName,
                    ]);
                }
            }

            // Reset account status: clear review_notes and set is_active to false
            // This will put the account back in "pending" status for admin review
            $user->review_notes = null;
            $user->is_active = false;
            $user->save();

            \Log::info('✅ National ID photos resubmitted', [
                'user_id' => $user->id,
                'front_photo' => $user->national_id_front_photo,
                'back_photo' => $user->national_id_back_photo,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم رفع صور البطاقة الشخصية بنجاح. سيتم مراجعتها من قبل الإدارة',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Resubmit national ID error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في رفع الصور',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

