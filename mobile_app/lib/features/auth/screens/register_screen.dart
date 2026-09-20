import 'dart:io';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/gestures.dart';
import 'package:image_picker/image_picker.dart';
import 'package:email_validator/email_validator.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _ageController = TextEditingController();
  final _universityIdController = TextEditingController();
  final _apiService = ApiService();
  final _imagePicker = ImagePicker();

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _acceptTerms = false;

  File? _nationalIdFrontPhoto;
  File? _nationalIdBackPhoto;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _ageController.dispose();
    _universityIdController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final DateTime now = DateTime.now();
    final DateTime initialDate = now.subtract(const Duration(days: 365 * 20)); // 20 years ago
    final DateTime firstDate = DateTime(1900);
    final DateTime lastDate = now.subtract(const Duration(days: 365 * 16)); // Minimum 16 years old

    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: firstDate,
      lastDate: lastDate,
      locale: const Locale('ar', 'EG'),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: Color(AppConstants.primaryColor),
              onPrimary: Color(AppConstants.secondaryColor),
              onSurface: Colors.black,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _ageController.text = DateFormat('yyyy/MM/dd').format(picked);
      });
    }
  }

  Future<void> _pickNationalIdImage({required bool isFront}) async {
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
          if (isFront) {
            _nationalIdFrontPhoto = File(image.path);
          } else {
            _nationalIdBackPhoto = File(image.path);
          }
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('حدث خطأ في اختيار الصورة: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _openTermsAndConditions() async {
    try {
      final uri = Uri.parse('https://linerscoot.com/privacy.html');
      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication,
        );
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('لا يمكن فتح صفحة الشروط والأحكام'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ في فتح الصفحة: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // Check if user accepted terms and conditions
    if (!_acceptTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى الموافقة على الشروط والأحكام للمتابعة'),
          backgroundColor: Colors.orange,
          duration: Duration(seconds: 3),
        ),
      );
      return;
    }

    if (_nationalIdFrontPhoto == null || _nationalIdBackPhoto == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى رفع صورة البطاقة الشخصية (الوجه الأمامي والخلفي)'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await _apiService.register(
        name: _nameController.text.trim(),
        phone: _phoneController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text,

        age: _ageController.text.trim(),
        universityId: _universityIdController.text.trim(),
        nationalIdFrontPhoto: _nationalIdFrontPhoto,
        nationalIdBackPhoto: _nationalIdBackPhoto,
      );

      if (!mounted) return;

      if (response.containsKey('message')) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              response['message'] ?? 'تم إنشاء الحساب بنجاح. يرجى انتظار تفعيل الحساب من قبل الإدارة.',
            ),
            backgroundColor: Colors.green,
            duration: const Duration(seconds: 4),
          ),
        );

        // Navigate to login after 2 seconds
        await Future.delayed(const Duration(seconds: 2));
        if (!mounted) return;
        Navigator.pushReplacementNamed(context, '/login');
      }
    } catch (e) {
      if (!mounted) return;

      print('❌ Registration error caught: $e');
      print('❌ Error type: ${e.runtimeType}');

      // Extract user-friendly error message
      String errorMessage = 'حدث خطأ في إنشاء الحساب';
      final errorStr = e.toString();

      print('📝 Error string: $errorStr');

      // Try to extract message from exception
      if (errorStr.contains('Exception: ')) {
        final parts = errorStr.split('Exception: ');
        if (parts.length > 1) {
          errorMessage = parts[1].trim();
          // Remove any pipe-separated additional info
          if (errorMessage.contains('|')) {
            errorMessage = errorMessage.split('|')[0].trim();
          }
          // Remove newlines and clean up
          errorMessage = errorMessage.replaceAll('\n', ' ').trim();
        }
      } else if (errorStr.contains('message')) {
        // Try to extract from JSON-like string
        final match = RegExp(r'"message"\s*:\s*"([^"]+)"').firstMatch(errorStr);
        if (match != null) {
          errorMessage = match.group(1)!;
        }
      } else if (errorStr.contains('errors')) {
        // Handle validation errors
        try {
          final errorsMatch = RegExp(r'"errors"\s*:\s*\{([^}]+)\}').firstMatch(errorStr);
          if (errorsMatch != null) {
            errorMessage = 'البيانات المدخلة غير صحيحة. يرجى التحقق من جميع الحقول.';
          }
        } catch (_) {
          // Keep default message
        }
      }

      // Check for specific error types
      if (errorStr.contains('email') && (errorStr.contains('already') || errorStr.contains('مستخدم') || errorStr.contains('unique'))) {
        errorMessage = 'البريد الإلكتروني مستخدم بالفعل';
      } else if (errorStr.contains('phone') && (errorStr.contains('already') || errorStr.contains('مستخدم') || errorStr.contains('unique'))) {
        errorMessage = 'رقم الهاتف مستخدم بالفعل';
      } else if (errorStr.contains('validation') || errorStr.contains('البيانات غير صحيحة')) {
        errorMessage = 'البيانات المدخلة غير صحيحة. يرجى التحقق من جميع الحقول.';
      } else if (errorStr.contains('SQLSTATE') || errorStr.contains('Duplicate entry')) {
        if (errorStr.contains('email')) {
          errorMessage = 'البريد الإلكتروني مستخدم بالفعل';
        } else if (errorStr.contains('phone')) {
          errorMessage = 'رقم الهاتف مستخدم بالفعل';
        } else {
          errorMessage = 'البيانات المدخلة مستخدمة بالفعل. يرجى التحقق من البيانات.';
        }
      } else if (errorStr.contains('storage') || errorStr.contains('file') || errorStr.contains('upload')) {
        errorMessage = 'خطأ في رفع الصور. يرجى المحاولة مرة أخرى';
      }

      print('✅ Final error message: $errorMessage');

      // Show error in a dialog for better visibility
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('خطأ في إنشاء الحساب'),
          content: Text(errorMessage),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('حسناً'),
            ),
          ],
        ),
      );

      // Also show snackbar
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errorMessage),
          backgroundColor: Colors.red,
          duration: const Duration(seconds: 5),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: Color(AppConstants.secondaryColor),
          ),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Logo
                Image.asset(
                  'assets/images/logo.png',
                  height: 100,
                  width: 100,
                ),

                const SizedBox(height: 24),

                // Title
                const Text(
                  'إنشاء حساب جديد',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Color(AppConstants.secondaryColor),
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 32),

                // Name Field
                TextFormField(
                  controller: _nameController,
                  decoration: InputDecoration(
                    labelText: 'الاسم',
                    prefixIcon: const Icon(Icons.person),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال الاسم';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Phone Field
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: InputDecoration(
                    labelText: 'رقم الهاتف',
                    prefixIcon: const Icon(Icons.phone),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال رقم الهاتف';
                    }
                    if (value.length < 10) {
                      return 'رقم الهاتف غير صحيح';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Email Field (University Email)
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: InputDecoration(
                    labelText: 'الإيميل الجامعي',
                    hintText: 'example@university.edu',
                    prefixIcon: const Icon(Icons.email),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال الإيميل الجامعي';
                    }
                    if (!EmailValidator.validate(value)) {
                      return 'الإيميل الجامعي غير صحيح';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Date of Birth Field (YYYY/MM/DD)
                TextFormField(
                  controller: _ageController,
                  readOnly: true,
                  onTap: _selectDate,
                  decoration: InputDecoration(
                    labelText: 'تاريخ الميلاد (YYYY/MM/DD)',
                    hintText: 'اضغط لاختيار التاريخ',
                    prefixIcon: const Icon(Icons.calendar_today),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال تاريخ الميلاد';
                    }

                    final regex = RegExp(r'^\d{4}/\d{2}/\d{2}$');
                    if (!regex.hasMatch(value.trim())) {
                      return 'صيغة التاريخ يجب أن تكون YYYY/MM/DD';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // University ID Field
                TextFormField(
                  controller: _universityIdController,
                  decoration: InputDecoration(
                    labelText: 'الرقم الجامعي',
                    prefixIcon: const Icon(Icons.school),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال الرقم الجامعي';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Password Field
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  decoration: InputDecoration(
                    labelText: 'كلمة المرور',
                    prefixIcon: const Icon(Icons.lock),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword ? Icons.visibility : Icons.visibility_off,
                      ),
                      onPressed: () {
                        setState(() {
                          _obscurePassword = !_obscurePassword;
                        });
                      },
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'يرجى إدخال كلمة المرور';
                    }
                    if (value.length < 6) {
                      return 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Confirm Password Field
                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: _obscureConfirmPassword,
                  decoration: InputDecoration(
                    labelText: 'تأكيد كلمة المرور',
                    prefixIcon: const Icon(Icons.lock_outline),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                      ),
                      onPressed: () {
                        setState(() {
                          _obscureConfirmPassword = !_obscureConfirmPassword;
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
                    if (value != _passwordController.text) {
                      return 'كلمة المرور غير متطابقة';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 24),

                // National ID Photos (Front & Back)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'صورة البطاقة الشخصية (الوجهين)',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () => _pickNationalIdImage(isFront: true),
                            child: Container(
                              height: 150,
                              margin: const EdgeInsets.only(right: 4),
                              decoration: BoxDecoration(
                                border: Border.all(
                                  color: Colors.grey,
                                  width: 2,
                                  style: BorderStyle.solid,
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: _nationalIdFrontPhoto == null
                                  ? const Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.credit_card,
                                          size: 38,
                                          color: Colors.grey,
                                        ),
                                        SizedBox(height: 8),
                                        Text(
                                          'الوجه الأمامي',
                                          style: TextStyle(color: Colors.grey),
                                        ),
                                      ],
                                    )
                                  : ClipRRect(
                                      borderRadius: BorderRadius.circular(10),
                                      child: Image.file(
                                        _nationalIdFrontPhoto!,
                                        fit: BoxFit.cover,
                                        width: double.infinity,
                                      ),
                                    ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: GestureDetector(
                            onTap: () => _pickNationalIdImage(isFront: false),
                            child: Container(
                              height: 150,
                              margin: const EdgeInsets.only(left: 4),
                              decoration: BoxDecoration(
                                border: Border.all(
                                  color: Colors.grey,
                                  width: 2,
                                  style: BorderStyle.solid,
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: _nationalIdBackPhoto == null
                                  ? const Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.credit_card,
                                          size: 38,
                                          color: Colors.grey,
                                        ),
                                        SizedBox(height: 8),
                                        Text(
                                          'الوجه الخلفي',
                                          style: TextStyle(color: Colors.grey),
                                        ),
                                      ],
                                    )
                                  : ClipRRect(
                                      borderRadius: BorderRadius.circular(10),
                                      child: Image.file(
                                        _nationalIdBackPhoto!,
                                        fit: BoxFit.cover,
                                        width: double.infinity,
                                      ),
                                    ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),

                const SizedBox(height: 24),

                // Terms and Conditions Checkbox
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  textDirection: ui.TextDirection.rtl,
                  children: [
                    Checkbox(
                      value: _acceptTerms,
                      onChanged: (value) {
                        setState(() {
                          _acceptTerms = value ?? false;
                        });
                      },
                      activeColor: const Color(AppConstants.primaryColor),
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      visualDensity: VisualDensity.compact,
                    ),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8.0),
                        child: RichText(
                          textAlign: TextAlign.right,
                          text: TextSpan(
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[800],
                              height: 1.5,
                            ),
                            children: [
                              TextSpan(
                                text: 'أوافق على ',
                                recognizer: TapGestureRecognizer()
                                  ..onTap = () {
                                    setState(() {
                                      _acceptTerms = !_acceptTerms;
                                    });
                                  },
                              ),
                              TextSpan(
                                text: 'الشروط والأحكام',
                                style: const TextStyle(
                                  color: Color(AppConstants.primaryColor),
                                  fontWeight: FontWeight.bold,
                                  decoration: TextDecoration.underline,
                                ),
                                recognizer: TapGestureRecognizer()
                                  ..onTap = () {
                                    _openTermsAndConditions();
                                  },
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // Register Button
                ElevatedButton(
                  onPressed: (_isLoading || !_acceptTerms) ? null : _register,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(AppConstants.primaryColor),
                    foregroundColor: const Color(AppConstants.secondaryColor),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    disabledBackgroundColor: Colors.grey[300],
                    disabledForegroundColor: Colors.grey[600],
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              Color(AppConstants.secondaryColor),
                            ),
                          ),
                        )
                      : const Text(
                          'إنشاء حساب',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),

                const SizedBox(height: 16),

                // Login Link
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text(
                      'لديك حساب بالفعل؟ ',
                      style: TextStyle(color: Colors.grey),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.pushReplacementNamed(context, '/login');
                      },
                      child: const Text(
                        'تسجيل الدخول',
                        style: TextStyle(
                          color: Color(AppConstants.primaryColor),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

