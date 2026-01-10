import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/models/user_model.dart';
import '../../../core/services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _apiService = ApiService();
  final _imagePicker = ImagePicker();
  final _formKey = GlobalKey<FormState>();
  
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  
  UserModel? _user;
  bool _isLoading = true;
  bool _isUpdatingPassword = false;
  bool _isUpdatingAvatar = false;
  bool _obscureCurrentPassword = true;
  bool _obscureNewPassword = true;
  bool _obscureConfirmPassword = true;
  File? _selectedAvatar;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _loadUserData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final user = await _apiService.getCurrentUser();
      print('📋 Loaded user data in ProfileScreen:');
      print('  - Age: ${user.age}');
      print('  - University ID: ${user.universityId}');
      print('  - Age is null: ${user.age == null}');
      print('  - Age > 0: ${user.age != null && user.age! > 0}');
      print('  - University ID is null: ${user.universityId == null}');
      print('  - University ID is not empty: ${user.universityId != null && user.universityId!.isNotEmpty}');
      
      if (mounted) {
        setState(() {
          _user = user;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('❌ Error loading user data in ProfileScreen: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في تحميل البيانات: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _pickAvatar() async {
    try {
      // Use camera instead of gallery to avoid READ_MEDIA_IMAGES permission
      final XFile? image = await _imagePicker.pickImage(
        source: ImageSource.camera,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 80,
      );

      if (image != null) {
        setState(() {
          _selectedAvatar = File(image.path);
        });
        // Upload immediately
        await _updateAvatar();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في اختيار الصورة: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _updateAvatar() async {
    if (_selectedAvatar == null) return;

    final selectedFile = _selectedAvatar!; // Save reference before clearing
    setState(() {
      _isUpdatingAvatar = true;
    });

    try {
      print('📸 Starting avatar upload...');
      final updatedUser = await _apiService.updateAvatar(selectedFile);
      print('✅ Avatar upload successful');
      print('📋 Updated user avatar: ${updatedUser.avatar}');
      
      if (mounted) {
        // Update user data immediately
        setState(() {
          _user = updatedUser;
          _isUpdatingAvatar = false;
        });
        
        // Wait a bit then reload to ensure server has processed the image
        await Future.delayed(const Duration(milliseconds: 500));
        
        // Reload user data to get the latest avatar URL from server
        await _loadUserData();
        
        // Clear selected avatar after successful update and reload
        if (mounted) {
          setState(() {
            _selectedAvatar = null;
          });
        }
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('تم تحديث الصورة الشخصية بنجاح'),
              backgroundColor: Colors.green,
              duration: Duration(seconds: 2),
            ),
          );
        }
      }
    } catch (e) {
      print('❌ Error updating avatar: $e');
      if (mounted) {
        setState(() {
          _isUpdatingAvatar = false;
          // Keep selected avatar on error so user can retry
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في تحديث الصورة: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }

  Future<void> _updatePassword() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isUpdatingPassword = true;
    });

    try {
      await _apiService.updatePassword(
        currentPassword: _currentPasswordController.text,
        newPassword: _newPasswordController.text,
      );

      if (mounted) {
        setState(() {
          _isUpdatingPassword = false;
          _currentPasswordController.clear();
          _newPasswordController.clear();
          _confirmPasswordController.clear();
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم تحديث كلمة المرور بنجاح'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isUpdatingPassword = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في تحديث كلمة المرور: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  String _getImageUrl(String? imagePath) {
    if (imagePath == null || imagePath.isEmpty) return '';
    if (imagePath.startsWith('http')) {
      // If it's already a full URL, just add cache buster
      final separator = imagePath.contains('?') ? '&' : '?';
      return '$imagePath${separator}t=${DateTime.now().millisecondsSinceEpoch}';
    }
    // Remove 'public/' prefix if exists (Laravel storage path format)
    String cleanPath = imagePath;
    if (cleanPath.startsWith('public/')) {
      cleanPath = cleanPath.replaceFirst('public/', '');
    }
    // Build full URL
    final baseUrl = '${ApiConstants.baseUrl.replaceAll('/api', '')}/storage/$cleanPath';
    final separator = baseUrl.contains('?') ? '&' : '?';
    return '$baseUrl${separator}t=${DateTime.now().millisecondsSinceEpoch}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الملف الشخصي'),
        backgroundColor: Color(AppConstants.primaryColor),
        foregroundColor: Color(AppConstants.secondaryColor),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _user == null
              ? const Center(child: Text('لا توجد بيانات'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Avatar Section
                        Center(
                          child: Stack(
                            children: [
                              Container(
                                width: 120,
                                height: 120,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: Color(AppConstants.primaryColor),
                                    width: 3,
                                  ),
                                ),
                                child: ClipOval(
                                  child: _isUpdatingAvatar && _selectedAvatar != null
                                      ? Image.file(
                                          _selectedAvatar!,
                                          fit: BoxFit.cover,
                                        )
                                      : _selectedAvatar != null
                                          ? Image.file(
                                              _selectedAvatar!,
                                              fit: BoxFit.cover,
                                            )
                                          : _user!.avatar != null &&
                                                  _user!.avatar!.isNotEmpty
                                              ? CachedNetworkImage(
                                                  imageUrl: _getImageUrl(
                                                    _user!.avatar,
                                                  ),
                                                  fit: BoxFit.cover,
                                                  cacheKey: _user!.avatar, // Use avatar path as cache key
                                                  placeholder: (context, url) =>
                                                      Container(
                                                    color: Colors.grey[300],
                                                    child: const Icon(
                                                      Icons.person,
                                                      size: 60,
                                                      color: Colors.grey,
                                                    ),
                                                  ),
                                                  errorWidget:
                                                      (context, url, error) {
                                                    print('❌ Error loading avatar: $error');
                                                    print('📸 Avatar URL: $url');
                                                    print('📸 Avatar path from user: ${_user!.avatar}');
                                                    // If error is 403 or 404, show placeholder
                                                    return Container(
                                                      color: Colors.grey[300],
                                                      child: const Icon(
                                                        Icons.person,
                                                        size: 60,
                                                        color: Colors.grey,
                                                      ),
                                                    );
                                                  },
                                                )
                                              : Container(
                                                  color: Colors.grey[300],
                                                  child: const Icon(
                                                    Icons.person,
                                                    size: 60,
                                                    color: Colors.grey,
                                                  ),
                                                ),
                                ),
                              ),
                              Positioned(
                                bottom: 0,
                                right: 0,
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: Color(AppConstants.primaryColor),
                                    shape: BoxShape.circle,
                                    border: Border.all(
                                      color: Colors.white,
                                      width: 2,
                                    ),
                                  ),
                                  child: _isUpdatingAvatar
                                      ? const Padding(
                                          padding: EdgeInsets.all(8.0),
                                          child: SizedBox(
                                            width: 20,
                                            height: 20,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2,
                                              valueColor:
                                                  AlwaysStoppedAnimation<Color>(
                                                Colors.white,
                                              ),
                                            ),
                                          ),
                                        )
                                      : IconButton(
                                          icon: const Icon(
                                            Icons.camera_alt,
                                            color: Colors.white,
                                            size: 20,
                                          ),
                                          onPressed: _pickAvatar,
                                        ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 32),

                        // User Info (Read-only)
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'معلومات الحساب',
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                _buildReadOnlyField(
                                  'البريد الإلكتروني',
                                  _user!.email,
                                  Icons.email,
                                ),
                                const SizedBox(height: 16),
                                _buildReadOnlyField(
                                  'رقم الهاتف',
                                  _user!.phone ?? 'غير متوفر',
                                  Icons.phone,
                                ),
                                // Always show university ID field (even if null/empty, show "غير متوفر")
                                const SizedBox(height: 16),
                                _buildReadOnlyField(
                                  'الرقم الجامعي',
                                  _user!.universityId != null && _user!.universityId!.isNotEmpty
                                      ? _user!.universityId!
                                      : 'غير متوفر',
                                  Icons.school,
                                ),
                                // Always show age field (even if null/0, show "غير متوفر")
                                const SizedBox(height: 16),
                                _buildReadOnlyField(
                                  'السن',
                                  _user!.age != null && _user!.age! > 0
                                      ? '${_user!.age!} سنة'
                                      : 'غير متوفر',
                                  Icons.calendar_today,
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Password Update Section
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'تغيير كلمة المرور',
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _currentPasswordController,
                                  obscureText: _obscureCurrentPassword,
                                  decoration: InputDecoration(
                                    labelText: 'كلمة المرور الحالية',
                                    prefixIcon: const Icon(Icons.lock),
                                    suffixIcon: IconButton(
                                      icon: Icon(
                                        _obscureCurrentPassword
                                            ? Icons.visibility
                                            : Icons.visibility_off,
                                      ),
                                      onPressed: () {
                                        setState(() {
                                          _obscureCurrentPassword =
                                              !_obscureCurrentPassword;
                                        });
                                      },
                                    ),
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.isEmpty) {
                                      return 'يرجى إدخال كلمة المرور الحالية';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _newPasswordController,
                                  obscureText: _obscureNewPassword,
                                  decoration: InputDecoration(
                                    labelText: 'كلمة المرور الجديدة',
                                    prefixIcon: const Icon(Icons.lock_outline),
                                    suffixIcon: IconButton(
                                      icon: Icon(
                                        _obscureNewPassword
                                            ? Icons.visibility
                                            : Icons.visibility_off,
                                      ),
                                      onPressed: () {
                                        setState(() {
                                          _obscureNewPassword =
                                              !_obscureNewPassword;
                                        });
                                      },
                                    ),
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.isEmpty) {
                                      return 'يرجى إدخال كلمة المرور الجديدة';
                                    }
                                    if (value.length < 8) {
                                      return 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  controller: _confirmPasswordController,
                                  obscureText: _obscureConfirmPassword,
                                  decoration: InputDecoration(
                                    labelText: 'تأكيد كلمة المرور الجديدة',
                                    prefixIcon: const Icon(Icons.lock_outline),
                                    suffixIcon: IconButton(
                                      icon: Icon(
                                        _obscureConfirmPassword
                                            ? Icons.visibility
                                            : Icons.visibility_off,
                                      ),
                                      onPressed: () {
                                        setState(() {
                                          _obscureConfirmPassword =
                                              !_obscureConfirmPassword;
                                        });
                                      },
                                    ),
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  validator: (value) {
                                    if (value == null || value.isEmpty) {
                                      return 'يرجى تأكيد كلمة المرور';
                                    }
                                    if (value != _newPasswordController.text) {
                                      return 'كلمة المرور غير متطابقة';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 24),
                                SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton(
                                    onPressed:
                                        _isUpdatingPassword ? null : _updatePassword,
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor:
                                          Color(AppConstants.primaryColor),
                                      foregroundColor:
                                          Color(AppConstants.secondaryColor),
                                      padding: const EdgeInsets.symmetric(
                                        vertical: 16,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
                                    child: _isUpdatingPassword
                                        ? const SizedBox(
                                            width: 20,
                                            height: 20,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2,
                                              valueColor:
                                                  AlwaysStoppedAnimation<Color>(
                                                Colors.white,
                                              ),
                                            ),
                                          )
                                        : const Text(
                                            'تحديث كلمة المرور',
                                            style: TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildReadOnlyField(String label, String value, IconData icon) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 14,
            color: Colors.grey[600],
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.grey[100],
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.grey[300]!),
          ),
          child: Row(
            children: [
              Icon(icon, color: Colors.grey[600], size: 20),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  value,
                  style: const TextStyle(
                    fontSize: 16,
                    color: Colors.black87,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

