<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LinerScoot') }} - تأجير السكوترات الذكية</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFD600',
                        secondary: '#000000',
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'ui-sans-serif', 'system-ui'],
                    },
                },
            },
        }
    </script>
    
    <style>
        * {
            font-family: 'Tajawal', sans-serif !important;
        }
        
        body {
            font-family: 'Tajawal', sans-serif !important;
            direction: rtl;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #FFD600 0%, #FFA500 100%) !important;
        }
        
        .scooter-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background-color: #FFD600 !important;
            color: #000000 !important;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #FFC700 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 214, 0, 0.4);
        }
        
        .btn-secondary {
            background-color: transparent !important;
            color: #000000 !important;
            border: 2px solid #000000 !important;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background-color: #000000 !important;
            color: #FFD600 !important;
            border-color: #000000 !important;
        }
        
        /* Ensure Tailwind utilities work */
        .bg-\[\#FFD600\] {
            background-color: #FFD600 !important;
        }
        
        .text-\[\#FFD600\] {
            color: #FFD600 !important;
        }
        
        .bg-\[\#FFC700\] {
            background-color: #FFC700 !important;
        }
        
        .hover\:bg-\[\#FFC700\]:hover {
            background-color: #FFC700 !important;
        }
        
        .hover\:text-\[\#FFD600\]:hover {
            color: #FFD600 !important;
        }
        
        .border-\[\#FFD600\] {
            border-color: #FFD600 !important;
        }
        
        .hover\:bg-\[\#FFD600\]:hover {
            background-color: #FFD600 !important;
        }
        
        .hover\:text-black:hover {
            color: #000000 !important;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-black">
                            <span class="text-[#FFD600]">Liner</span>Scoot
                        </h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary px-4 py-2 font-semibold rounded-lg">
                            لوحة التحكم
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-black font-semibold hover:text-[#FFD600] transition-colors duration-300">
                            تسجيل الدخول
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary px-6 py-2 font-semibold rounded-lg">
                                إنشاء حساب
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-right">
                    <h1 class="text-5xl lg:text-6xl font-bold text-black mb-6 leading-tight">
                        استأجر سكوترك الذكي
                        <span class="block text-[#FFD600]">في دقائق</span>
                    </h1>
                    <p class="text-xl text-gray-800 mb-8 leading-relaxed">
                        حلول ذكية لتأجير السكوترات. استكشف مدينتك بطريقة مبتكرة وآمنة مع نظام متكامل للتحكم والتتبع.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary px-8 py-4 rounded-lg font-bold text-lg text-center">
                                ابدأ الآن
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn-primary px-8 py-4 rounded-lg font-bold text-lg text-center">
                                إنشاء حساب
                            </a>
                            <a href="{{ route('login') }}" class="btn-secondary px-8 py-4 rounded-lg font-bold text-lg text-center">
                                تسجيل الدخول
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="scooter-icon">
                        <svg width="400" height="400" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Scooter Body -->
                            <rect x="100" y="200" width="200" height="60" rx="30" fill="#000000"/>
                            <!-- Wheels -->
                            <circle cx="130" cy="280" r="40" fill="#000000"/>
                            <circle cx="270" cy="280" r="40" fill="#000000"/>
                            <circle cx="130" cy="280" r="25" fill="#FFD600"/>
                            <circle cx="270" cy="280" r="25" fill="#FFD600"/>
                            <!-- Handlebar -->
                            <rect x="280" y="150" width="10" height="50" fill="#000000"/>
                            <rect x="250" y="150" width="50" height="10" fill="#000000"/>
                            <!-- Seat -->
                            <ellipse cx="150" cy="200" rx="30" ry="15" fill="#000000"/>
                            <!-- Decorative Elements -->
                            <circle cx="200" cy="230" r="15" fill="#FFD600"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-black mb-4">لماذا LinerScoot؟</h2>
                <p class="text-xl text-gray-600">حلول مبتكرة لتجربة تأجير سكوترات استثنائية</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">تتبع GPS مباشر</h3>
                    <p class="text-gray-600 leading-relaxed">
                        تتبع موقع السكوترات في الوقت الفعلي مع نظام GPS متقدم لضمان الأمان والراحة.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">نظام أمان متقدم</h3>
                    <p class="text-gray-600 leading-relaxed">
                        نظام مكافحة السرقة مع قفل/فتح ذكي وتنبيهات فورية لأي حركة غير مصرح بها.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">دفع سريع وسهل</h3>
                    <p class="text-gray-600 leading-relaxed">
                        محفظة إلكترونية مدمجة مع نظام نقاط الولاء واشتراكات مرنة لجميع احتياجاتك.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">مناطق جغرافية ذكية</h3>
                    <p class="text-gray-600 leading-relaxed">
                        تحديد مناطق مسموحة وممنوعة مع نظام Geo-Fencing متقدم لضمان الاستخدام الآمن.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">إدارة الوقت</h3>
                    <p class="text-gray-600 leading-relaxed">
                        تأجير بالدقيقة مع اشتراكات مرنة (30 دقيقة، 100 دقيقة، أو غير محدود).
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-[#FFD600] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-black mb-4">صيانة مستمرة</h3>
                    <p class="text-gray-600 leading-relaxed">
                        نظام صيانة شامل مع تتبع حالة البطارية والإصلاحات لضمان جودة الخدمة.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-[#FFD600] mb-6">
                جاهز للبدء؟
            </h2>
            <p class="text-xl text-white mb-8 max-w-2xl mx-auto">
                انضم إلينا اليوم واستمتع بتجربة تأجير سكوترات ذكية وآمنة
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary px-8 py-4 rounded-lg font-bold text-lg">
                        اذهب إلى لوحة التحكم
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary px-8 py-4 rounded-lg font-bold text-lg">
                        إنشاء حساب مجاني
                    </a>
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-lg font-bold text-lg border-2 border-[#FFD600] text-[#FFD600] hover:bg-[#FFD600] hover:text-black transition-all duration-300">
                        تسجيل الدخول
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">
                        <span class="text-[#FFD600]">Liner</span>Scoot
                    </h3>
                    <p class="text-gray-400">
                        حلول ذكية لتأجير السكوترات مع نظام متكامل للتحكم والتتبع.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-[#FFD600] transition">عن المشروع</a></li>
                        <li><a href="#" class="hover:text-[#FFD600] transition">المميزات</a></li>
                        <li><a href="#" class="hover:text-[#FFD600] transition">الأسعار</a></li>
                        <li><a href="#" class="hover:text-[#FFD600] transition">اتصل بنا</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">تابعنا</h4>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-[#FFD600] rounded-full flex items-center justify-center text-black hover:bg-[#FFC700] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-[#FFD600] rounded-full flex items-center justify-center text-black hover:bg-[#FFC700] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} LinerScoot. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
</body>
</html>
