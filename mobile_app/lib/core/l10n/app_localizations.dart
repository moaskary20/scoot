import 'package:flutter/material.dart';

class AppLocalizations {
  final Locale locale;

  AppLocalizations(this.locale);

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate = _AppLocalizationsDelegate();

  // Common translations
  String get appName => _localizedValues[locale.languageCode]?['appName'] ?? 'Liner Scoot';
  String get home => _localizedValues[locale.languageCode]?['home'] ?? 'الرئيسية';
  String get wallet => _localizedValues[locale.languageCode]?['wallet'] ?? 'محفظة';
  String get trips => _localizedValues[locale.languageCode]?['trips'] ?? 'رحلاتي';
  String get profile => _localizedValues[locale.languageCode]?['profile'] ?? 'الملف الشخصي';
  String get safeRidingGuide => _localizedValues[locale.languageCode]?['safeRidingGuide'] ?? 'دليل الركوب الآمن';
  String get ridingGuideLine1 => _localizedValues[locale.languageCode]?['ridingGuideLine1'] ?? 'يجب أن يكون عمرك 16 سنة أو أكثر.';
  String get ridingGuideLine2 => _localizedValues[locale.languageCode]?['ridingGuideLine2'] ?? 'الركوب لشخص واحد فقط على السكوتر.';
  String get ridingGuideLine3 => _localizedValues[locale.languageCode]?['ridingGuideLine3'] ?? 'ارتدِ خوذة الحماية دائمًا أثناء الركوب.';
  String get ridingGuideLine4 => _localizedValues[locale.languageCode]?['ridingGuideLine4'] ?? 'التزم بالقيادة داخل المنطقة البرتقالية (جامعة الجلالة) فقط.';
  String get ridingGuideLine5 => _localizedValues[locale.languageCode]?['ridingGuideLine5'] ?? 'سر دائمًا على يمين الطريق وبعيدًا عن السيارات.';
  String get ridingGuideLine6 => _localizedValues[locale.languageCode]?['ridingGuideLine6'] ?? 'ممنوع صعود السكوتر على الرصيف أو السير عكس الاتجاه.';
  String get ridingGuideLine7 => _localizedValues[locale.languageCode]?['ridingGuideLine7'] ?? 'أركن السكوتر داخل المنطقة الخضراء وفي الأماكن المخصصة فقط.';
  String get ridingGuideLine8 => _localizedValues[locale.languageCode]?['ridingGuideLine8'] ?? 'تأكد من أن القفل مغلق بإحكام.';
  String get ridingGuideLine9 => _localizedValues[locale.languageCode]?['ridingGuideLine9'] ?? 'التقط صورة واضحة للسكوتر بعد الركن.';
  String get freeBalance => _localizedValues[locale.languageCode]?['freeBalance'] ?? 'رصيد مجاني';
  String get chargeBalance => _localizedValues[locale.languageCode]?['chargeBalance'] ?? 'اشحن رصيدك';
  String get user => _localizedValues[locale.languageCode]?['user'] ?? 'مستخدم';
  String get noPhoneNumber => _localizedValues[locale.languageCode]?['noPhoneNumber'] ?? 'لا يوجد رقم هاتف';
  String get howToRide => _localizedValues[locale.languageCode]?['howToRide'] ?? 'إزاي تركب لينر سكوت';
  String get language => _localizedValues[locale.languageCode]?['language'] ?? 'اللغة';
  String get selectLanguage => _localizedValues[locale.languageCode]?['selectLanguage'] ?? 'اختر اللغة';
  String get arabic => _localizedValues[locale.languageCode]?['arabic'] ?? 'العربية';
  String get english => _localizedValues[locale.languageCode]?['english'] ?? 'English';
  String get cancel => _localizedValues[locale.languageCode]?['cancel'] ?? 'إلغاء';
  String get ok => _localizedValues[locale.languageCode]?['ok'] ?? 'حسناً';
  String get save => _localizedValues[locale.languageCode]?['save'] ?? 'حفظ';
  String get loading => _localizedValues[locale.languageCode]?['loading'] ?? 'جاري التحميل...';
  String get error => _localizedValues[locale.languageCode]?['error'] ?? 'خطأ';
  String get success => _localizedValues[locale.languageCode]?['success'] ?? 'نجح';
  String get close => _localizedValues[locale.languageCode]?['close'] ?? 'إغلاق';
  String get back => _localizedValues[locale.languageCode]?['back'] ?? 'رجوع';
  String get logout => _localizedValues[locale.languageCode]?['logout'] ?? 'تسجيل الخروج';
  String get activeTrip => _localizedValues[locale.languageCode]?['activeTrip'] ?? 'رحلة نشطة';
  String get startTrip => _localizedValues[locale.languageCode]?['startTrip'] ?? 'ابدأ الرحلة';
  String get endTrip => _localizedValues[locale.languageCode]?['endTrip'] ?? 'إنهاء الرحلة';
  String get duration => _localizedValues[locale.languageCode]?['duration'] ?? 'المدة';
  String get cost => _localizedValues[locale.languageCode]?['cost'] ?? 'التكلفة';
  String get minutes => _localizedValues[locale.languageCode]?['minutes'] ?? 'دقيقة';
  String get egp => _localizedValues[locale.languageCode]?['egp'] ?? 'ج.م';
  
  // Home Screen
  String get noScootersAvailable => _localizedValues[locale.languageCode]?['noScootersAvailable'] ?? 'لا توجد سكوترات متاحة في المنطقة القريبة';
  String get errorLoadingScooters => _localizedValues[locale.languageCode]?['errorLoadingScooters'] ?? 'حدث خطأ في تحميل السكوترات';
  String get errorStartingTrip => _localizedValues[locale.languageCode]?['errorStartingTrip'] ?? 'حدث خطأ في بدء الرحلة';
  String get help => _localizedValues[locale.languageCode]?['help'] ?? 'المساعدة';
  String get howCanWeHelp => _localizedValues[locale.languageCode]?['howCanWeHelp'] ?? 'كيف يمكننا مساعدتك؟';
  String get errorLoadingUserData => _localizedValues[locale.languageCode]?['errorLoadingUserData'] ?? 'حدث خطأ في تحميل بيانات المستخدم';
  String get scanAndStartTrip => _localizedValues[locale.languageCode]?['scanAndStartTrip'] ?? 'سكان وابدأ الرحلة';
  String get scooter => _localizedValues[locale.languageCode]?['scooter'] ?? 'سكوتر';
  String get available => _localizedValues[locale.languageCode]?['available'] ?? 'متاح';
  String get unavailable => _localizedValues[locale.languageCode]?['unavailable'] ?? 'غير متاح';
  String get locked => _localizedValues[locale.languageCode]?['locked'] ?? 'مقفول';
  String get unlocked => _localizedValues[locale.languageCode]?['unlocked'] ?? 'مفتوح';
  String get battery => _localizedValues[locale.languageCode]?['battery'] ?? 'البطارية';
  String get km => _localizedValues[locale.languageCode]?['km'] ?? 'كم';
  String get yourLocation => _localizedValues[locale.languageCode]?['yourLocation'] ?? 'موقعك الحالي';
  
  // Wallet Screen
  String get walletBalance => _localizedValues[locale.languageCode]?['walletBalance'] ?? 'رصيد المحفظة';
  String get availableBalance => _localizedValues[locale.languageCode]?['availableBalance'] ?? 'الرصيد المتاح';
  String get charge => _localizedValues[locale.languageCode]?['charge'] ?? 'اشحن';
  String get history => _localizedValues[locale.languageCode]?['history'] ?? 'تاريخ';
  String get addCard => _localizedValues[locale.languageCode]?['addCard'] ?? 'اضف كارت';
  String get promoCode => _localizedValues[locale.languageCode]?['promoCode'] ?? 'كود البرومو';
  String get enterPromoCode => _localizedValues[locale.languageCode]?['enterPromoCode'] ?? 'يرجى إدخال كود البرومو';
  String get errorLoadingWallet => _localizedValues[locale.languageCode]?['errorLoadingWallet'] ?? 'حدث خطأ في تحميل بيانات المحفظة';
  String get cardSavedSuccessfully => _localizedValues[locale.languageCode]?['cardSavedSuccessfully'] ?? 'تم حفظ الكارت بنجاح';
  String get errorOccurred => _localizedValues[locale.languageCode]?['errorOccurred'] ?? 'حدث خطأ';
  
  // Transaction Types
  String get financialTransactions => _localizedValues[locale.languageCode]?['financialTransactions'] ?? 'المعاملات المالية';
  String get noTransactionsYet => _localizedValues[locale.languageCode]?['noTransactionsYet'] ?? 'لا توجد معاملات مالية بعد';
  String get transactionsWillAppearHere => _localizedValues[locale.languageCode]?['transactionsWillAppearHere'] ?? 'ستظهر جميع المعاملات المالية المتعلقة بالمحفظة هنا';
  String get walletTopUp => _localizedValues[locale.languageCode]?['walletTopUp'] ?? 'شحن المحفظة';
  String get tripPayment => _localizedValues[locale.languageCode]?['tripPayment'] ?? 'دفع رحلة';
  String get refund => _localizedValues[locale.languageCode]?['refund'] ?? 'استرجاع';
  String get adjustment => _localizedValues[locale.languageCode]?['adjustment'] ?? 'تعديل';
  String get subscription => _localizedValues[locale.languageCode]?['subscription'] ?? 'اشتراك';
  String get transaction => _localizedValues[locale.languageCode]?['transaction'] ?? 'معاملة';
  String get pending => _localizedValues[locale.languageCode]?['pending'] ?? 'قيد الانتظار';
  String get failed => _localizedValues[locale.languageCode]?['failed'] ?? 'فاشلة';
  String get activate => _localizedValues[locale.languageCode]?['activate'] ?? 'تفعيل';
  String get addPromoCode => _localizedValues[locale.languageCode]?['addPromoCode'] ?? 'ضيف البروموكود';
  String get promoCodeHint => _localizedValues[locale.languageCode]?['promoCodeHint'] ?? 'بروموكود';
  String get viewAndRedeemPoints => _localizedValues[locale.languageCode]?['viewAndRedeemPoints'] ?? 'عرض نقاطي واستبدالها';
  String get redeemLoyaltyBalance => _localizedValues[locale.languageCode]?['redeemLoyaltyBalance'] ?? 'استبدل نقاط الولاء برصيد في المحفظة';
  String get promoCodeActivated => _localizedValues[locale.languageCode]?['promoCodeActivated'] ?? 'تم تفعيل الكود بنجاح';
  String get invalidPromoCode => _localizedValues[locale.languageCode]?['invalidPromoCode'] ?? 'كود غير صحيح';
  
  String getTransactionTypeText(String type) {
    switch (type) {
      case 'top_up':
        return _localizedValues[locale.languageCode]?['walletTopUp'] ?? 'شحن المحفظة';
      case 'trip_payment':
        return _localizedValues[locale.languageCode]?['tripPayment'] ?? 'دفع رحلة';
      case 'refund':
        return _localizedValues[locale.languageCode]?['refund'] ?? 'استرجاع';
      case 'adjustment':
        return _localizedValues[locale.languageCode]?['adjustment'] ?? 'تعديل';
      case 'penalty':
        return _localizedValues[locale.languageCode]?['penalty'] ?? 'الغرامة';
      case 'subscription':
        return _localizedValues[locale.languageCode]?['subscription'] ?? 'اشتراك';
      default:
        return _localizedValues[locale.languageCode]?['transaction'] ?? 'معاملة';
    }
  }
  
  String getTransactionStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return _localizedValues[locale.languageCode]?['completed'] ?? 'مكتملة';
      case 'pending':
        return _localizedValues[locale.languageCode]?['pending'] ?? 'قيد الانتظار';
      case 'failed':
        return _localizedValues[locale.languageCode]?['failed'] ?? 'فاشلة';
      case 'cancelled':
        return _localizedValues[locale.languageCode]?['cancelled'] ?? 'ملغاة';
      default:
        return status;
    }
  }
  
  // Top Up Screen
  String get topUp => _localizedValues[locale.languageCode]?['topUp'] ?? 'شحن الرصيد';
  String get topUpBalance => _localizedValues[locale.languageCode]?['topUpBalance'] ?? 'شحن الرصيد';
  String get enterAmountToCharge => _localizedValues[locale.languageCode]?['enterAmountToCharge'] ?? 'أدخل المبلغ المراد شحنه';
  String get minimumChargeAmount => _localizedValues[locale.languageCode]?['minimumChargeAmount'] ?? 'الحد الأدنى للشحن: 1 جنيه';
  String get amountLabel => _localizedValues[locale.languageCode]?['amountLabel'] ?? 'المبلغ (جنيه)';
  String get amountHint => _localizedValues[locale.languageCode]?['amountHint'] ?? '0.00';
  String get pleaseEnterAmount => _localizedValues[locale.languageCode]?['pleaseEnterAmount'] ?? 'يرجى إدخال المبلغ';
  String get pleaseEnterValidNumber => _localizedValues[locale.languageCode]?['pleaseEnterValidNumber'] ?? 'يرجى إدخال رقم صحيح';
  String get minimumChargeRequired => _localizedValues[locale.languageCode]?['minimumChargeRequired'] ?? 'الحد الأدنى للشحن هو 1 جنيه';
  String get availablePaymentMethods => _localizedValues[locale.languageCode]?['availablePaymentMethods'] ?? 'طرق الدفع المتاحة';
  String get visaMastercard => _localizedValues[locale.languageCode]?['visaMastercard'] ?? 'الفيزا / الماستر كارد';
  String get bankWallet => _localizedValues[locale.languageCode]?['bankWallet'] ?? 'المحفظة البنكية';
  String get pay => _localizedValues[locale.languageCode]?['pay'] ?? 'دفع';
  String get paymentError => _localizedValues[locale.languageCode]?['paymentError'] ?? 'حدث خطأ في إنشاء عملية الدفع';
  String get enterAmount => _localizedValues[locale.languageCode]?['enterAmount'] ?? 'أدخل المبلغ';
  String get pleaseEnterValidAmount => _localizedValues[locale.languageCode]?['pleaseEnterValidAmount'] ?? 'يرجى إدخال مبلغ صحيح';
  String get minimumCharge => _localizedValues[locale.languageCode]?['minimumCharge'] ?? 'الحد الأدنى للشحن هو 1 جنيه';
  String get confirmPayment => _localizedValues[locale.languageCode]?['confirmPayment'] ?? 'تأكيد الدفع';
  String get paymentPageWillOpen => _localizedValues[locale.languageCode]?['paymentPageWillOpen'] ?? 'سيتم فتح صفحة الدفع لشحن رصيدك بمبلغ';
  String get continueText => _localizedValues[locale.languageCode]?['continueText'] ?? 'متابعة';
  String get paymentPageOpened => _localizedValues[locale.languageCode]?['paymentPageOpened'] ?? 'تم فتح صفحة الدفع. بعد إتمام الدفع، سيتم تحديث رصيدك تلقائياً';
  String get cannotOpenPaymentPage => _localizedValues[locale.languageCode]?['cannotOpenPaymentPage'] ?? 'لا يمكن فتح صفحة الدفع';
  
  // Transaction History Screen
  String get transactionHistory => _localizedValues[locale.languageCode]?['transactionHistory'] ?? 'سجل المعاملات';
  String get errorLoadingTransactions => _localizedValues[locale.languageCode]?['errorLoadingTransactions'] ?? 'حدث خطأ في تحميل المعاملات';
  
  // Add Card Screen
  String get addNewCard => _localizedValues[locale.languageCode]?['addNewCard'] ?? 'إضافة كارت جديد';
  String get cardNumber => _localizedValues[locale.languageCode]?['cardNumber'] ?? 'رقم الكارت';
  String get cardHolderName => _localizedValues[locale.languageCode]?['cardHolderName'] ?? 'اسم حامل الكارت';
  String get expiryDate => _localizedValues[locale.languageCode]?['expiryDate'] ?? 'تاريخ الانتهاء';
  String get cvv => _localizedValues[locale.languageCode]?['cvv'] ?? 'CVV';
  String get setAsDefault => _localizedValues[locale.languageCode]?['setAsDefault'] ?? 'استخدام ككارت افتراضي';
  
  // Free Balance Screen
  String get referralCodeNotFound => _localizedValues[locale.languageCode]?['referralCodeNotFound'] ?? 'لم يتم العثور على كود إحالة. يرجى المحاولة مرة أخرى.';
  String get errorLoadingData => _localizedValues[locale.languageCode]?['errorLoadingData'] ?? 'حدث خطأ في تحميل البيانات';
  String get noReferralCodeAvailable => _localizedValues[locale.languageCode]?['noReferralCodeAvailable'] ?? 'لا يوجد كود إحالة متاح. يرجى المحاولة مرة أخرى.';
  
  // Profile Screen
  String get noData => _localizedValues[locale.languageCode]?['noData'] ?? 'لا توجد بيانات';
  String get accountInfo => _localizedValues[locale.languageCode]?['accountInfo'] ?? 'معلومات الحساب';
  String get email => _localizedValues[locale.languageCode]?['email'] ?? 'البريد الإلكتروني';
  String get phoneNumber => _localizedValues[locale.languageCode]?['phoneNumber'] ?? 'رقم الهاتف';
  String get universityId => _localizedValues[locale.languageCode]?['universityId'] ?? 'الرقم الجامعي';
  String get age => _localizedValues[locale.languageCode]?['age'] ?? 'السن';
  String get notAvailable => _localizedValues[locale.languageCode]?['notAvailable'] ?? 'غير متوفر';
  String get year => _localizedValues[locale.languageCode]?['year'] ?? 'سنة';
  String get years => _localizedValues[locale.languageCode]?['years'] ?? 'سنة';
  String get changePassword => _localizedValues[locale.languageCode]?['changePassword'] ?? 'تغيير كلمة المرور';
  String get currentPassword => _localizedValues[locale.languageCode]?['currentPassword'] ?? 'كلمة المرور الحالية';
  String get newPassword => _localizedValues[locale.languageCode]?['newPassword'] ?? 'كلمة المرور الجديدة';
  String get confirmNewPassword => _localizedValues[locale.languageCode]?['confirmNewPassword'] ?? 'تأكيد كلمة المرور الجديدة';
  String get pleaseEnterCurrentPassword => _localizedValues[locale.languageCode]?['pleaseEnterCurrentPassword'] ?? 'يرجى إدخال كلمة المرور الحالية';
  String get pleaseEnterNewPassword => _localizedValues[locale.languageCode]?['pleaseEnterNewPassword'] ?? 'يرجى إدخال كلمة المرور الجديدة';
  String get pleaseConfirmPassword => _localizedValues[locale.languageCode]?['pleaseConfirmPassword'] ?? 'يرجى تأكيد كلمة المرور';
  String get passwordMinLength => _localizedValues[locale.languageCode]?['passwordMinLength'] ?? 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
  String get passwordMismatch => _localizedValues[locale.languageCode]?['passwordMismatch'] ?? 'كلمة المرور غير متطابقة';
  String get updatePassword => _localizedValues[locale.languageCode]?['updatePassword'] ?? 'تحديث كلمة المرور';
  String get passwordUpdatedSuccessfully => _localizedValues[locale.languageCode]?['passwordUpdatedSuccessfully'] ?? 'تم تحديث كلمة المرور بنجاح';
  String get passwordUpdateError => _localizedValues[locale.languageCode]?['passwordUpdateError'] ?? 'حدث خطأ في تحديث كلمة المرور';
  String get updateAvatar => _localizedValues[locale.languageCode]?['updateAvatar'] ?? 'تحديث الصورة الشخصية';
  String get avatarUpdatedSuccessfully => _localizedValues[locale.languageCode]?['avatarUpdatedSuccessfully'] ?? 'تم تحديث الصورة الشخصية بنجاح';
  String get avatarUpdateError => _localizedValues[locale.languageCode]?['avatarUpdateError'] ?? 'حدث خطأ في تحديث الصورة';
  String get pickImageError => _localizedValues[locale.languageCode]?['pickImageError'] ?? 'حدث خطأ في اختيار الصورة';
  String get dataLoadingError => _localizedValues[locale.languageCode]?['dataLoadingError'] ?? 'حدث خطأ في تحميل البيانات';
  String get accountStatus => _localizedValues[locale.languageCode]?['accountStatus'] ?? 'حالة الحساب';
  String get rejected => _localizedValues[locale.languageCode]?['rejected'] ?? 'مرفوض';
  String get reviewNotes => _localizedValues[locale.languageCode]?['reviewNotes'] ?? 'ملاحظات المراجعة:';
  String get resubmitNationalIdTitle => _localizedValues[locale.languageCode]?['resubmitNationalIdTitle'] ?? 'رفع صورة البطاقة الشخصية مرة أخرى';
  String get accountRejectedMessage => _localizedValues[locale.languageCode]?['accountRejectedMessage'] ?? 'تم رفض حسابك. يرجى رفع صور البطاقة الشخصية مرة أخرى للمراجعة.';
  String get frontSide => _localizedValues[locale.languageCode]?['frontSide'] ?? 'الوجه الأمامي';
  String get backSide => _localizedValues[locale.languageCode]?['backSide'] ?? 'الوجه الخلفي';
  String get uploadPhotos => _localizedValues[locale.languageCode]?['uploadPhotos'] ?? 'رفع الصور';
  String get pleaseUploadBothSides => _localizedValues[locale.languageCode]?['pleaseUploadBothSides'] ?? 'يرجى رفع صورة البطاقة الشخصية (الوجه الأمامي والخلفي)';
  String get nationalIdUploadedSuccessfully => _localizedValues[locale.languageCode]?['nationalIdUploadedSuccessfully'] ?? 'تم رفع صور البطاقة الشخصية بنجاح';
  String get nationalIdUploadError => _localizedValues[locale.languageCode]?['nationalIdUploadError'] ?? 'حدث خطأ في رفع الصور';
  
  String formatAge(int age) {
    if (locale.languageCode == 'en') {
      return '$age ${age == 1 ? 'year' : 'years'}';
    } else {
      return '$age $year';
    }
  }
  String get shareYourCode => _localizedValues[locale.languageCode]?['shareYourCode'] ?? 'شارك الكود بتاعك';
  String get errorSharing => _localizedValues[locale.languageCode]?['errorSharing'] ?? 'حدث خطأ في المشاركة';
  String get referralCode => _localizedValues[locale.languageCode]?['referralCode'] ?? 'كود الإحالة';
  String get referredFriends => _localizedValues[locale.languageCode]?['referredFriends'] ?? 'الأصدقاء المحالون';
  String get totalEarned => _localizedValues[locale.languageCode]?['totalEarned'] ?? 'إجمالي المكتسب';
  String get getBalanceReward => _localizedValues[locale.languageCode]?['getBalanceReward'] ?? 'احصل على';
  String get balanceReward => _localizedValues[locale.languageCode]?['balanceReward'] ?? 'رصيد!';
  String get referFriendsDescription => _localizedValues[locale.languageCode]?['referFriendsDescription'] ?? 'رشح بحد أقصي';
  String get friends => _localizedValues[locale.languageCode]?['friends'] ?? 'أصدقاء';
  String get fromYourFriends => _localizedValues[locale.languageCode]?['fromYourFriends'] ?? 'من اصدقاءك لتحصل على';
  String get balanceWhenComplete => _localizedValues[locale.languageCode]?['balanceWhenComplete'] ?? 'ج.م رصيد لما يكملوا رحلتهم الأولى!';
  String get referralCodeLabel => _localizedValues[locale.languageCode]?['referralCodeLabel'] ?? 'كود الإحالة:';
  String get codeUsed => _localizedValues[locale.languageCode]?['codeUsed'] ?? 'تم استخدام الكود';
  String get receivedInFull => _localizedValues[locale.languageCode]?['receivedInFull'] ?? 'تم استلامه بالكامل';
  
  // Trips Screen
  String get myTrips => _localizedValues[locale.languageCode]?['myTrips'] ?? 'رحلاتي';
  String get noTripsFound => _localizedValues[locale.languageCode]?['noTripsFound'] ?? 'لا توجد رحلات';
  String get tripDetails => _localizedValues[locale.languageCode]?['tripDetails'] ?? 'تفاصيل الرحلة';
  String get startTime => _localizedValues[locale.languageCode]?['startTime'] ?? 'وقت البدء';
  String get endTime => _localizedValues[locale.languageCode]?['endTime'] ?? 'وقت الانتهاء';
  String get status => _localizedValues[locale.languageCode]?['status'] ?? 'الحالة';
  String get paymentStatus => _localizedValues[locale.languageCode]?['paymentStatus'] ?? 'حالة الدفع';
  String get baseCost => _localizedValues[locale.languageCode]?['baseCost'] ?? 'التكلفة الأساسية';
  String get discount => _localizedValues[locale.languageCode]?['discount'] ?? 'الخصم';
  String get penalty => _localizedValues[locale.languageCode]?['penalty'] ?? 'الغرامة';
  String get zoneExitDetected => _localizedValues[locale.languageCode]?['zoneExitDetected'] ?? 'تم اكتشاف خروج من المنطقة';
  String get completed => _localizedValues[locale.languageCode]?['completed'] ?? 'مكتملة';
  String get active => _localizedValues[locale.languageCode]?['active'] ?? 'نشطة';
  String get cancelled => _localizedValues[locale.languageCode]?['cancelled'] ?? 'ملغاة';
  String get paid => _localizedValues[locale.languageCode]?['paid'] ?? 'مدفوع';
  String get partiallyPaid => _localizedValues[locale.languageCode]?['partiallyPaid'] ?? 'مدفوع جزئياً';
  String get unpaid => _localizedValues[locale.languageCode]?['unpaid'] ?? 'غير مدفوع';
  String get noTripsYet => _localizedValues[locale.languageCode]?['noTripsYet'] ?? 'لم تقم بأي رحلات بعد';
  String get tripNumber => _localizedValues[locale.languageCode]?['tripNumber'] ?? 'رحلة رقم';
  String get outsideZone => _localizedValues[locale.languageCode]?['outsideZone'] ?? 'خارج المنطقة';
  String get paidAmount => _localizedValues[locale.languageCode]?['paidAmount'] ?? 'المدفوع';
  String get remainingAmount => _localizedValues[locale.languageCode]?['remainingAmount'] ?? 'المتبقي';
  String get total => _localizedValues[locale.languageCode]?['total'] ?? 'الإجمالي';
  String get tapToViewDetails => _localizedValues[locale.languageCode]?['tapToViewDetails'] ?? 'اضغط لعرض التفاصيل';
  String get fullyPaidMessage => _localizedValues[locale.languageCode]?['fullyPaidMessage'] ?? 'غير مدفوع بالكامل - يرجى سداد المبلغ المتبقي';
  String get partiallyPaidMessage => _localizedValues[locale.languageCode]?['partiallyPaidMessage'] ?? 'مدفوع جزئياً - يرجى سداد المبلغ المتبقي';
  String get errorLoadingTrips => _localizedValues[locale.languageCode]?['errorLoadingTrips'] ?? 'حدث خطأ في تحميل الرحلات';
  
  // Trip Details Screen
  String get tripInformation => _localizedValues[locale.languageCode]?['tripInformation'] ?? 'معلومات الرحلة';
  String get penaltyDetails => _localizedValues[locale.languageCode]?['penaltyDetails'] ?? 'تفاصيل الغرامة';
  String get paymentDetails => _localizedValues[locale.languageCode]?['paymentDetails'] ?? 'تفاصيل الدفع';
  String get startPoint => _localizedValues[locale.languageCode]?['startPoint'] ?? 'نقطة البداية';
  String get endPoint => _localizedValues[locale.languageCode]?['endPoint'] ?? 'نقطة النهاية';
  String get notes => _localizedValues[locale.languageCode]?['notes'] ?? 'ملاحظات';
  String get penaltyDefault => _localizedValues[locale.languageCode]?['penaltyDefault'] ?? 'غرامة';
  String get penaltyDescription => _localizedValues[locale.languageCode]?['penaltyDescription'] ?? 'وصف الغرامة:';
  String get penaltyType => _localizedValues[locale.languageCode]?['penaltyType'] ?? 'النوع:';
  String get appliedDate => _localizedValues[locale.languageCode]?['appliedDate'] ?? 'تاريخ التطبيق:';
  String get zoneExitMessage => _localizedValues[locale.languageCode]?['zoneExitMessage'] ?? 'تم اكتشاف خروج من المنطقة المسموحة';
  String get totalCost => _localizedValues[locale.languageCode]?['totalCost'] ?? 'إجمالي التكلفة';
  String get hours => _localizedValues[locale.languageCode]?['hours'] ?? 'ساعة';
  String formatDurationText(int hours, int minutes) {
    if (locale.languageCode == 'en') {
      final hoursText = hours == 1 ? 'hour' : 'hours';
      final minutesText = minutes == 1 ? 'minute' : 'minutes';
      return '$hours $hoursText $minutes $minutesText';
    } else {
      final template = _localizedValues[locale.languageCode]?['durationFormat'] ?? '{hours} ساعة {minutes} دقيقة';
      return template.replaceAll('{hours}', hours.toString()).replaceAll('{minutes}', minutes.toString());
    }
  }
  
  String formatMinutesText(int minutes) {
    if (locale.languageCode == 'en') {
      final minutesText = minutes == 1 ? 'minute' : 'minutes';
      return '$minutes $minutesText';
    } else {
      final template = _localizedValues[locale.languageCode]?['minutesFormat'] ?? '{minutes} دقيقة';
      return template.replaceAll('{minutes}', minutes.toString());
    }
  }
  
  String formatHoursText(int hours) {
    if (locale.languageCode == 'en') {
      final hoursText = hours == 1 ? 'hour' : 'hours';
      return '$hours $hoursText';
    } else {
      final template = _localizedValues[locale.languageCode]?['hoursFormat'] ?? '{hours} ساعة';
      return template.replaceAll('{hours}', hours.toString());
    }
  }
  
  // Penalty Types
  String get penaltyTypeZoneExit => _localizedValues[locale.languageCode]?['penaltyTypeZoneExit'] ?? 'خروج من المنطقة';
  String get penaltyTypeForbiddenParking => _localizedValues[locale.languageCode]?['penaltyTypeForbiddenParking'] ?? 'ركن في مكان محظور';
  String get penaltyTypeUnlockedScooter => _localizedValues[locale.languageCode]?['penaltyTypeUnlockedScooter'] ?? 'عدم قفل السكوتر';
  String get penaltyTypeOther => _localizedValues[locale.languageCode]?['penaltyTypeOther'] ?? 'أخرى';
  
  // Penalty Statuses
  String get penaltyStatusPending => _localizedValues[locale.languageCode]?['penaltyStatusPending'] ?? 'قيد الانتظار';
  String get penaltyStatusWaived => _localizedValues[locale.languageCode]?['penaltyStatusWaived'] ?? 'ملغاة';
  
  // QR Scanner Screen
  String get scanQRCode => _localizedValues[locale.languageCode]?['scanQRCode'] ?? 'امسح رمز QR';
  String get scanQRCodeToStart => _localizedValues[locale.languageCode]?['scanQRCodeToStart'] ?? 'امسح رمز QR للبدء';
  String get positionCamera => _localizedValues[locale.languageCode]?['positionCamera'] ?? 'ضع الكاميرا على رمز QR';
  
  // Active Trip Screen
  String get tripDuration => _localizedValues[locale.languageCode]?['tripDuration'] ?? 'مدة الرحلة';
  String get closeTrip => _localizedValues[locale.languageCode]?['closeTrip'] ?? 'إغلاق الرحلة';
  String get confirmCloseTrip => _localizedValues[locale.languageCode]?['confirmCloseTrip'] ?? 'هل أنت متأكد من إغلاق الرحلة؟';
  String get costCalculationMessage => _localizedValues[locale.languageCode]?['costCalculationMessage'] ?? 'سيتم حساب التكلفة حسب المنطقة الجغرافية';
  String get tripClosedSuccessfully => _localizedValues[locale.languageCode]?['tripClosedSuccessfully'] ?? 'تم إغلاق الرحلة بنجاح';
  String get backToHome => _localizedValues[locale.languageCode]?['backToHome'] ?? 'العودة إلى الصفحة الرئيسية';
  String get tripCompletionError => _localizedValues[locale.languageCode]?['tripCompletionError'] ?? 'خطأ في إغلاق الرحلة';
  String get tripCompletionErrorMessage => _localizedValues[locale.languageCode]?['tripCompletionErrorMessage'] ?? 'حدث خطأ أثناء إغلاق الرحلة:';
  String get returnToHomeQuestion => _localizedValues[locale.languageCode]?['returnToHomeQuestion'] ?? 'هل تريد العودة إلى الصفحة الرئيسية؟';
  String get stayHere => _localizedValues[locale.languageCode]?['stayHere'] ?? 'البقاء هنا';
  String get warning => _localizedValues[locale.languageCode]?['warning'] ?? 'تحذير';
  String get cannotCloseTripMessage => _localizedValues[locale.languageCode]?['cannotCloseTripMessage'] ?? 'لا يمكنك إغلاق هذه الشاشة أثناء الرحلة. استخدم زر "إغلاق الرحلة" لإتمام الرحلة.';
  String get cannotCancelTripMessage => _localizedValues[locale.languageCode]?['cannotCancelTripMessage'] ?? 'لا يمكنك إلغاء الرحلة من هنا. يرجى استخدام زر "إغلاق الرحلة" لإتمامها.';

  // Loyalty
  String get loyaltyPoints => _localizedValues[locale.languageCode]?['loyaltyPoints'] ?? 'نقاط الولاء';
  String get loyaltyLevel => _localizedValues[locale.languageCode]?['loyaltyLevel'] ?? 'مستوى الولاء';
  String get loyaltyTransactions => _localizedValues[locale.languageCode]?['loyaltyTransactions'] ?? 'معاملات نقاط الولاء';
  String get noLoyaltyTransactionsYet => _localizedValues[locale.languageCode]?['noLoyaltyTransactionsYet'] ?? 'لا توجد معاملات نقاط حتى الآن';
  String get pointsLabel => _localizedValues[locale.languageCode]?['pointsLabel'] ?? 'نقاط';
  String get currentLevel => _localizedValues[locale.languageCode]?['currentLevel'] ?? 'المستوى الحالي';
  String get maxLevelReached => _localizedValues[locale.languageCode]?['maxLevelReached'] ?? 'أعلى مستوى ولاء 🎉';
  String get loyaltyProgressDescription => _localizedValues[locale.languageCode]?['loyaltyProgressDescription'] ?? 'كل ما تكمل رحلات أكتر، تجمع نقاط أكتر وتطلع لمستوى أعلى.';
  String get loyaltyEarned => _localizedValues[locale.languageCode]?['loyaltyEarned'] ?? 'نقاط مكتسبة';
  String get loyaltyRedeemed => _localizedValues[locale.languageCode]?['loyaltyRedeemed'] ?? 'نقاط مستخدمة';
  String get loyaltyRedeem => _localizedValues[locale.languageCode]?['loyaltyRedeem'] ?? 'استبدال نقاط الولاء';
  String get loyaltyAdjusted => _localizedValues[locale.languageCode]?['loyaltyAdjusted'] ?? 'تعديل نقاط';
  String get loyaltyExpired => _localizedValues[locale.languageCode]?['loyaltyExpired'] ?? 'نقاط منتهية';
  String get balanceAfter => _localizedValues[locale.languageCode]?['balanceAfter'] ?? 'الرصيد بعد';
  String get canRedeemLoyaltyPoints => _localizedValues[locale.languageCode]?['canRedeemLoyaltyPoints'] ?? 'يمكنك استبدال نقاط الولاء برصيد في المحفظة';
  String get pointsNotEnough => _localizedValues[locale.languageCode]?['pointsNotEnough'] ?? 'النقاط غير كافية';
  String get bronze => _localizedValues[locale.languageCode]?['bronze'] ?? 'برونزي';
  String get silver => _localizedValues[locale.languageCode]?['silver'] ?? 'فضي';
  String get gold => _localizedValues[locale.languageCode]?['gold'] ?? 'ذهبي';
  
  String needMorePointsToRedeemText(int points) {
    final template = _localizedValues[locale.languageCode]?['needMorePointsToRedeem'] ?? 'تحتاج {points} نقطة إضافية للاستبدال';
    return template.replaceAll('{points}', points.toString());
  }
  
  // Loyalty Redemption
  String get pointsToRedeem => _localizedValues[locale.languageCode]?['pointsToRedeem'] ?? 'عدد النقاط المراد استبدالها';
  String get egpAmount => _localizedValues[locale.languageCode]?['egpAmount'] ?? 'المبلغ المضاف للمحفظة';
  String get redeemNow => _localizedValues[locale.languageCode]?['redeemNow'] ?? 'استبدال النقاط';
  String get redeemDisabled => _localizedValues[locale.languageCode]?['redeemDisabled'] ?? 'استبدال النقاط معطل حالياً';
  String get insufficientPoints => _localizedValues[locale.languageCode]?['insufficientPoints'] ?? 'ليس لديك نقاط كافية';
  String get invalidPointsAmount => _localizedValues[locale.languageCode]?['invalidPointsAmount'] ?? 'يرجى إدخال رقم صحيح';
  String get redeemFailed => _localizedValues[locale.languageCode]?['redeemFailed'] ?? 'حدث خطأ في استبدال النقاط';
  String get minPointsRequired => _localizedValues[locale.languageCode]?['minPointsRequired'] ?? 'الحد الأدنى لاستبدال النقاط هو';
  String get redeemConfirmation => _localizedValues[locale.languageCode]?['redeemConfirmation'] ?? 'تأكيد الاستبدال';
  String get redeemSuccess => _localizedValues[locale.languageCode]?['redeemSuccess'] ?? 'تم الاستبدال بنجاح';
  String get currentPointsLabel => _localizedValues[locale.languageCode]?['currentPointsLabel'] ?? 'نقاطك الحالية';
  String get pointsLabelSingle => _localizedValues[locale.languageCode]?['pointsLabelSingle'] ?? 'نقطة';
  String get newPointsBalance => _localizedValues[locale.languageCode]?['newPointsBalance'] ?? 'النقاط الجديدة';
  String get newWalletBalance => _localizedValues[locale.languageCode]?['newWalletBalance'] ?? 'رصيد المحفظة الجديد';
  String get enterPointsToRedeem => _localizedValues[locale.languageCode]?['enterPointsToRedeem'] ?? 'أدخل عدد النقاط';
  String get calculating => _localizedValues[locale.languageCode]?['calculating'] ?? 'جاري الحساب...';
  String get redeemConfirmationQuestion => _localizedValues[locale.languageCode]?['redeemConfirmationQuestion'] ?? 'هل أنت متأكد من استبدال:';
  String get pointsToRedeemLabel => _localizedValues[locale.languageCode]?['pointsToRedeemLabel'] ?? 'النقاط المستبدلة:';
  String get walletAmountAfterRedeem => _localizedValues[locale.languageCode]?['walletAmountAfterRedeem'] ?? 'المبلغ المضاف للمحفظة:';
  String get redeemNotesTitle => _localizedValues[locale.languageCode]?['redeemNotesTitle'] ?? 'ملاحظات مهمة:';
  String redeemNotesText(int minPoints, int rate) {
    final template = _localizedValues[locale.languageCode]?['redeemNotes'] ?? '• الحد الأدنى لاستبدال النقاط هو {minPoints} نقطة\n• كل {rate} نقطة = 1 جنيه\n• سيتم إضافة المبلغ مباشرة إلى المحفظة\n• لا يمكن استرجاع النقاط بعد الاستبدال';
    return template.replaceAll('{minPoints}', minPoints.toString()).replaceAll('{rate}', rate.toString());
  }
  
  String pointsToEgpRateText(int rate) {
    final template = _localizedValues[locale.languageCode]?['pointsToEgpRate'] ?? '{rate} نقطة = 1 جنيه';
    return template.replaceAll('{rate}', rate.toString());
  }
  
  String minRedeemText(int minPoints) {
    final template = _localizedValues[locale.languageCode]?['minRedeem'] ?? 'الحد الأدنى للاستبدال: {minPoints} نقطة';
    return template.replaceAll('{minPoints}', minPoints.toString());
  }
  
  String availablePointsText(int points) {
    final template = _localizedValues[locale.languageCode]?['availablePoints'] ?? 'النقاط المتاحة: {points}';
    return template.replaceAll('{points}', points.toString());
  }
  
  String insufficientPointsMessage(int current, int required) {
    final template = _localizedValues[locale.languageCode]?['insufficientPointsMessage'] ?? 'ليس لديك نقاط كافية. النقاط المتاحة: {current}';
    return template.replaceAll('{current}', current.toString()).replaceAll('{required}', required.toString());
  }
  
  String minRedeemMessage(int minPoints) {
    final template = _localizedValues[locale.languageCode]?['minRedeemMessage'] ?? 'الحد الأدنى لاستبدال النقاط هو {minPoints} نقطة';
    return template.replaceAll('{minPoints}', minPoints.toString());
  }

  String pointsToNextLevel(int points) =>
      _localizedValues[locale.languageCode]?['pointsToNextLevel']?.replaceFirst('{points}', points.toString()) ??
      'متبقي $points نقطة للمستوى التالي';

  static final Map<String, Map<String, String>> _localizedValues = {
    'ar': {
      'appName': 'لينر سكوت',
      'home': 'الرئيسية',
      'wallet': 'محفظة',
      'trips': 'رحلاتي',
      'profile': 'الملف الشخصي',
      'safeRidingGuide': 'دليل الركوب الآمن',
      'ridingGuideLine1': 'يجب أن يكون عمرك 16 سنة أو أكثر.',
      'ridingGuideLine2': 'الركوب لشخص واحد فقط على السكوتر.',
      'ridingGuideLine3': 'ارتدِ خوذة الحماية دائمًا أثناء الركوب.',
      'ridingGuideLine4': 'التزم بالقيادة داخل المنطقة البرتقالية (جامعة الجلالة) فقط.',
      'ridingGuideLine5': 'سر دائمًا على يمين الطريق وبعيدًا عن السيارات.',
      'ridingGuideLine6': 'ممنوع صعود السكوتر على الرصيف أو السير عكس الاتجاه.',
      'ridingGuideLine7': 'أركن السكوتر داخل المنطقة الخضراء وفي الأماكن المخصصة فقط.',
      'ridingGuideLine8': 'تأكد من أن القفل مغلق بإحكام.',
      'ridingGuideLine9': 'التقط صورة واضحة للسكوتر بعد الركن.',
      'freeBalance': 'رصيد مجاني',
      'chargeBalance': 'اشحن رصيدك',
      'user': 'مستخدم',
      'noPhoneNumber': 'لا يوجد رقم هاتف',
      'loyaltyPoints': 'نقاط الولاء',
      'loyaltyLevel': 'مستوى الولاء',
      'loyaltyTransactions': 'معاملات نقاط الولاء',
      'noLoyaltyTransactionsYet': 'لا توجد معاملات نقاط حتى الآن',
      'pointsLabel': 'نقاط',
      'currentLevel': 'المستوى الحالي',
      'maxLevelReached': 'أعلى مستوى ولاء 🎉',
      'loyaltyProgressDescription': 'كل ما تكمل رحلات أكتر، تجمع نقاط أكتر وتطلع لمستوى أعلى.',
      'loyaltyEarned': 'نقاط مكتسبة',
      'loyaltyRedeemed': 'نقاط مستخدمة',
      'loyaltyRedeem': 'استبدال نقاط الولاء',
      'loyaltyAdjusted': 'تعديل نقاط',
      'loyaltyExpired': 'نقاط منتهية',
      'balanceAfter': 'الرصيد بعد',
      'canRedeemLoyaltyPoints': 'يمكنك استبدال نقاط الولاء برصيد في المحفظة',
      'needMorePointsToRedeem': 'تحتاج {points} نقطة إضافية للاستبدال',
      'pointsNotEnough': 'النقاط غير كافية',
      'bronze': 'برونزي',
      'silver': 'فضي',
      'gold': 'ذهبي',
      'pointsToNextLevel': 'متبقي {points} نقطة للمستوى التالي',
      // Loyalty Redemption
      'pointsToRedeem': 'عدد النقاط المراد استبدالها',
      'egpAmount': 'المبلغ المضاف للمحفظة',
      'redeemNow': 'استبدال النقاط',
      'redeemDisabled': 'استبدال النقاط معطل حالياً',
      'insufficientPoints': 'ليس لديك نقاط كافية',
      'invalidPointsAmount': 'يرجى إدخال رقم صحيح',
      'redeemFailed': 'حدث خطأ في استبدال النقاط',
      'minPointsRequired': 'الحد الأدنى لاستبدال النقاط هو',
      'redeemConfirmation': 'تأكيد الاستبدال',
      'redeemSuccess': 'تم الاستبدال بنجاح',
      'currentPointsLabel': 'نقاطك الحالية',
      'pointsLabelSingle': 'نقطة',
      'newPointsBalance': 'النقاط الجديدة',
      'newWalletBalance': 'رصيد المحفظة الجديد',
      'enterPointsToRedeem': 'أدخل عدد النقاط',
      'calculating': 'جاري الحساب...',
      'redeemConfirmationQuestion': 'هل أنت متأكد من استبدال:',
      'pointsToRedeemLabel': 'النقاط المستبدلة:',
      'walletAmountAfterRedeem': 'المبلغ المضاف للمحفظة:',
      'redeemNotesTitle': 'ملاحظات مهمة:',
      'redeemNotes': '• الحد الأدنى لاستبدال النقاط هو {minPoints} نقطة\n• كل {rate} نقطة = 1 جنيه\n• سيتم إضافة المبلغ مباشرة إلى المحفظة\n• لا يمكن استرجاع النقاط بعد الاستبدال',
      'pointsToEgpRate': '{rate} نقطة = 1 جنيه',
      'minRedeem': 'الحد الأدنى للاستبدال: {minPoints} نقطة',
      'availablePoints': 'النقاط المتاحة: {points}',
      'insufficientPointsMessage': 'ليس لديك نقاط كافية. النقاط المتاحة: {current}',
      'minRedeemMessage': 'الحد الأدنى لاستبدال النقاط هو {minPoints} نقطة',
      'howToRide': 'إزاي تركب لينر سكوت',
      'language': 'اللغة',
      'selectLanguage': 'اختر اللغة',
      'arabic': 'العربية',
      'english': 'English',
      'cancel': 'إلغاء',
      'ok': 'حسناً',
      'save': 'حفظ',
      'loading': 'جاري التحميل...',
      'error': 'خطأ',
      'success': 'نجح',
      'close': 'إغلاق',
      'back': 'رجوع',
      'logout': 'تسجيل الخروج',
      'activeTrip': 'رحلة نشطة',
      'startTrip': 'ابدأ الرحلة',
      'endTrip': 'إنهاء الرحلة',
      'duration': 'المدة',
      'cost': 'التكلفة',
      'minutes': 'دقيقة',
      'egp': 'ج.م',
      'noScootersAvailable': 'لا توجد سكوترات متاحة في المنطقة القريبة',
      'errorLoadingScooters': 'حدث خطأ في تحميل السكوترات',
      'errorStartingTrip': 'حدث خطأ في بدء الرحلة',
      'help': 'المساعدة',
      'howCanWeHelp': 'كيف يمكننا مساعدتك؟',
      'errorLoadingUserData': 'حدث خطأ في تحميل بيانات المستخدم',
      'scanAndStartTrip': 'سكان وابدأ الرحلة',
      'scooter': 'سكوتر',
      'available': 'متاح',
      'unavailable': 'غير متاح',
      'locked': 'مقفول',
      'unlocked': 'مفتوح',
      'battery': 'البطارية',
      'km': 'كم',
      'yourLocation': 'موقعك الحالي',
      'walletBalance': 'رصيد المحفظة',
      'availableBalance': 'الرصيد المتاح',
      'charge': 'اشحن',
      'history': 'تاريخ',
      'addCard': 'اضف كارت',
      'promoCode': 'كود البرومو',
      'enterPromoCode': 'يرجى إدخال كود البرومو',
      'errorLoadingWallet': 'حدث خطأ في تحميل بيانات المحفظة',
      'cardSavedSuccessfully': 'تم حفظ الكارت بنجاح',
      'errorOccurred': 'حدث خطأ',
      'financialTransactions': 'المعاملات المالية',
      'noTransactionsYet': 'لا توجد معاملات مالية بعد',
      'transactionsWillAppearHere': 'ستظهر جميع المعاملات المالية المتعلقة بالمحفظة هنا',
      'walletTopUp': 'شحن المحفظة',
      'tripPayment': 'دفع رحلة',
      'refund': 'استرجاع',
      'adjustment': 'تعديل',
      'subscription': 'اشتراك',
      'transaction': 'معاملة',
      'pending': 'قيد الانتظار',
      'failed': 'فاشلة',
      'activate': 'تفعيل',
      'addPromoCode': 'ضيف البروموكود',
      'promoCodeHint': 'بروموكود',
      'viewAndRedeemPoints': 'عرض نقاطي واستبدالها',
      'redeemLoyaltyBalance': 'استبدل نقاط الولاء برصيد في المحفظة',
      'promoCodeActivated': 'تم تفعيل الكود بنجاح',
      'invalidPromoCode': 'كود غير صحيح',
      'topUp': 'شحن الرصيد',
      'topUpBalance': 'شحن الرصيد',
      'enterAmountToCharge': 'أدخل المبلغ المراد شحنه',
      'minimumChargeAmount': 'الحد الأدنى للشحن: 1 جنيه',
      'amountLabel': 'المبلغ (جنيه)',
      'amountHint': '0.00',
      'pleaseEnterAmount': 'يرجى إدخال المبلغ',
      'pleaseEnterValidNumber': 'يرجى إدخال رقم صحيح',
      'minimumChargeRequired': 'الحد الأدنى للشحن هو 1 جنيه',
      'availablePaymentMethods': 'طرق الدفع المتاحة',
      'visaMastercard': 'الفيزا / الماستر كارد',
      'bankWallet': 'المحفظة البنكية',
      'pay': 'دفع',
      'paymentError': 'حدث خطأ في إنشاء عملية الدفع',
      'enterAmount': 'أدخل المبلغ',
      'pleaseEnterValidAmount': 'يرجى إدخال مبلغ صحيح',
      'minimumCharge': 'الحد الأدنى للشحن هو 1 جنيه',
      'confirmPayment': 'تأكيد الدفع',
      'paymentPageWillOpen': 'سيتم فتح صفحة الدفع لشحن رصيدك بمبلغ',
      'continueText': 'متابعة',
      'paymentPageOpened': 'تم فتح صفحة الدفع. بعد إتمام الدفع، سيتم تحديث رصيدك تلقائياً',
      'cannotOpenPaymentPage': 'لا يمكن فتح صفحة الدفع',
      'transactionHistory': 'سجل المعاملات',
      'errorLoadingTransactions': 'حدث خطأ في تحميل المعاملات',
      'addNewCard': 'إضافة كارت جديد',
      'cardNumber': 'رقم الكارت',
      'cardHolderName': 'اسم حامل الكارت',
      'expiryDate': 'تاريخ الانتهاء',
      'cvv': 'CVV',
      'setAsDefault': 'استخدام ككارت افتراضي',
      'referralCodeNotFound': 'لم يتم العثور على كود إحالة. يرجى المحاولة مرة أخرى.',
      'errorLoadingData': 'حدث خطأ في تحميل البيانات',
      'noReferralCodeAvailable': 'لا يوجد كود إحالة متاح. يرجى المحاولة مرة أخرى.',
      // Profile Screen
      'noData': 'لا توجد بيانات',
      'accountInfo': 'معلومات الحساب',
      'email': 'البريد الإلكتروني',
      'phoneNumber': 'رقم الهاتف',
      'universityId': 'الرقم الجامعي',
      'age': 'السن',
      'notAvailable': 'غير متوفر',
      'year': 'سنة',
      'years': 'سنة',
      'changePassword': 'تغيير كلمة المرور',
      'currentPassword': 'كلمة المرور الحالية',
      'newPassword': 'كلمة المرور الجديدة',
      'confirmNewPassword': 'تأكيد كلمة المرور الجديدة',
      'pleaseEnterCurrentPassword': 'يرجى إدخال كلمة المرور الحالية',
      'pleaseEnterNewPassword': 'يرجى إدخال كلمة المرور الجديدة',
      'pleaseConfirmPassword': 'يرجى تأكيد كلمة المرور',
      'passwordMinLength': 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
      'passwordMismatch': 'كلمة المرور غير متطابقة',
      'updatePassword': 'تحديث كلمة المرور',
      'passwordUpdatedSuccessfully': 'تم تحديث كلمة المرور بنجاح',
      'passwordUpdateError': 'حدث خطأ في تحديث كلمة المرور',
      'updateAvatar': 'تحديث الصورة الشخصية',
      'avatarUpdatedSuccessfully': 'تم تحديث الصورة الشخصية بنجاح',
      'avatarUpdateError': 'حدث خطأ في تحديث الصورة',
      'pickImageError': 'حدث خطأ في اختيار الصورة',
      'dataLoadingError': 'حدث خطأ في تحميل البيانات',
      'accountStatus': 'حالة الحساب',
      'rejected': 'مرفوض',
      'reviewNotes': 'ملاحظات المراجعة:',
      'resubmitNationalIdTitle': 'رفع صورة البطاقة الشخصية مرة أخرى',
      'accountRejectedMessage': 'تم رفض حسابك. يرجى رفع صور البطاقة الشخصية مرة أخرى للمراجعة.',
      'frontSide': 'الوجه الأمامي',
      'backSide': 'الوجه الخلفي',
      'uploadPhotos': 'رفع الصور',
      'pleaseUploadBothSides': 'يرجى رفع صورة البطاقة الشخصية (الوجه الأمامي والخلفي)',
      'nationalIdUploadedSuccessfully': 'تم رفع صور البطاقة الشخصية بنجاح',
      'nationalIdUploadError': 'حدث خطأ في رفع الصور',
      'shareYourCode': 'شارك الكود بتاعك',
      'errorSharing': 'حدث خطأ في المشاركة',
      'referralCode': 'كود الإحالة',
      'referredFriends': 'الأصدقاء المحالون',
      'totalEarned': 'إجمالي المكتسب',
      'myTrips': 'رحلاتي',
      'noTripsFound': 'لا توجد رحلات',
      'tripDetails': 'تفاصيل الرحلة',
      'startTime': 'وقت البدء',
      'endTime': 'وقت الانتهاء',
      'status': 'الحالة',
      'paymentStatus': 'حالة الدفع',
      'baseCost': 'التكلفة الأساسية',
      'discount': 'الخصم',
      'penalty': 'الغرامة',
      'zoneExitDetected': 'تم اكتشاف خروج من المنطقة',
      'completed': 'مكتملة',
      'active': 'نشطة',
      'cancelled': 'ملغاة',
      'paid': 'مدفوع',
      'partiallyPaid': 'مدفوع جزئياً',
      'unpaid': 'غير مدفوع',
      'noTripsYet': 'لم تقم بأي رحلات بعد',
      'tripNumber': 'رحلة رقم',
      'outsideZone': 'خارج المنطقة',
      'paidAmount': 'المدفوع',
      'remainingAmount': 'المتبقي',
      'total': 'الإجمالي',
      'tapToViewDetails': 'اضغط لعرض التفاصيل',
      'fullyPaidMessage': 'غير مدفوع بالكامل - يرجى سداد المبلغ المتبقي',
      'partiallyPaidMessage': 'مدفوع جزئياً - يرجى سداد المبلغ المتبقي',
      'errorLoadingTrips': 'حدث خطأ في تحميل الرحلات',
      'tripInformation': 'معلومات الرحلة',
      'penaltyDetails': 'تفاصيل الغرامة',
      'paymentDetails': 'تفاصيل الدفع',
      'startPoint': 'نقطة البداية',
      'endPoint': 'نقطة النهاية',
      'notes': 'ملاحظات',
      'penaltyDefault': 'غرامة',
      'penaltyDescription': 'وصف الغرامة:',
      'penaltyType': 'النوع:',
      'appliedDate': 'تاريخ التطبيق:',
      'zoneExitMessage': 'تم اكتشاف خروج من المنطقة المسموحة',
      'totalCost': 'إجمالي التكلفة',
      'hours': 'ساعة',
      'durationFormat': '{hours} ساعة {minutes} دقيقة',
      'minutesFormat': '{minutes} دقيقة',
      'hoursFormat': '{hours} ساعة',
      'penaltyTypeZoneExit': 'خروج من المنطقة',
      'penaltyTypeForbiddenParking': 'ركن في مكان محظور',
      'penaltyTypeUnlockedScooter': 'عدم قفل السكوتر',
      'penaltyTypeOther': 'أخرى',
      'penaltyStatusPending': 'قيد الانتظار',
      'penaltyStatusWaived': 'ملغاة',
      'scanQRCode': 'امسح رمز QR',
      'scanQRCodeToStart': 'امسح رمز QR للبدء',
      'positionCamera': 'ضع الكاميرا على رمز QR',
      'tripDuration': 'مدة الرحلة',
      'closeTrip': 'إغلاق الرحلة',
      'confirmCloseTrip': 'هل أنت متأكد من إغلاق الرحلة؟',
      'costCalculationMessage': 'سيتم حساب التكلفة حسب المنطقة الجغرافية',
      'tripClosedSuccessfully': 'تم إغلاق الرحلة بنجاح',
      'backToHome': 'العودة إلى الصفحة الرئيسية',
      'tripCompletionError': 'خطأ في إغلاق الرحلة',
      'tripCompletionErrorMessage': 'حدث خطأ أثناء إغلاق الرحلة:',
      'returnToHomeQuestion': 'هل تريد العودة إلى الصفحة الرئيسية؟',
      'stayHere': 'البقاء هنا',
      'warning': 'تحذير',
      'cannotCloseTripMessage': 'لا يمكنك إغلاق هذه الشاشة أثناء الرحلة. استخدم زر "إغلاق الرحلة" لإتمام الرحلة.',
      'cannotCancelTripMessage': 'لا يمكنك إلغاء الرحلة من هنا. يرجى استخدام زر "إغلاق الرحلة" لإتمامها.',
    },
    'en': {
      'appName': 'Liner Scoot',
      'home': 'Home',
      'wallet': 'Wallet',
      'trips': 'My Trips',
      'profile': 'Profile',
      'safeRidingGuide': 'Safe Riding Guide',
      'ridingGuideLine1': 'You must be 16 years or older.',
      'ridingGuideLine2': 'Ride alone on the scooter only.',
      'ridingGuideLine3': 'Always wear a helmet while riding.',
      'ridingGuideLine4': 'Ride only within the orange zone (Galala University).',
      'ridingGuideLine5': 'Always ride on the right side of the road and away from cars.',
      'ridingGuideLine6': 'Do not ride on the sidewalk or in the opposite direction.',
      'ridingGuideLine7': 'Park the scooter only in the green zone and designated areas.',
      'ridingGuideLine8': 'Make sure the lock is securely closed.',
      'ridingGuideLine9': 'Take a clear photo of the scooter after parking.',
      'freeBalance': 'Free Balance',
      'chargeBalance': 'Charge Balance',
      'user': 'User',
      'noPhoneNumber': 'No phone number',
      'loyaltyPoints': 'Loyalty Points',
      'loyaltyLevel': 'Loyalty Level',
      'loyaltyTransactions': 'Loyalty Transactions',
      'noLoyaltyTransactionsYet': 'No loyalty transactions yet',
      'pointsLabel': 'Points',
      'currentLevel': 'Current Level',
      'maxLevelReached': 'Highest loyalty level 🎉',
      'loyaltyProgressDescription': 'The more you ride, the more points you earn and unlock higher levels.',
      'loyaltyEarned': 'Points earned',
      'loyaltyRedeemed': 'Points redeemed',
      'loyaltyRedeem': 'Redeem Loyalty Points',
      'loyaltyAdjusted': 'Points adjusted',
      'loyaltyExpired': 'Points expired',
      'balanceAfter': 'Balance After',
      'canRedeemLoyaltyPoints': 'You can redeem loyalty points for wallet balance',
      'needMorePointsToRedeem': 'You need {points} more point(s) to redeem',
      'pointsNotEnough': 'Insufficient Points',
      'bronze': 'Bronze',
      'silver': 'Silver',
      'gold': 'Gold',
      'pointsToNextLevel': '{points} points to the next level',
      // Loyalty Redemption
      'pointsToRedeem': 'Points to Redeem',
      'egpAmount': 'Amount to be Added to Wallet',
      'redeemNow': 'Redeem Points',
      'redeemDisabled': 'Points redemption is currently disabled',
      'insufficientPoints': 'Insufficient points',
      'invalidPointsAmount': 'Please enter a valid number',
      'redeemFailed': 'Failed to redeem points',
      'minPointsRequired': 'Minimum points required for redemption:',
      'redeemConfirmation': 'Confirm Redemption',
      'redeemSuccess': 'Redemption Successful',
      'currentPointsLabel': 'Your Current Points',
      'pointsLabelSingle': 'point',
      'newPointsBalance': 'New Points Balance',
      'newWalletBalance': 'New Wallet Balance',
      'enterPointsToRedeem': 'Enter points amount',
      'calculating': 'Calculating...',
      'redeemConfirmationQuestion': 'Are you sure you want to redeem:',
      'pointsToRedeemLabel': 'Points to Redeem:',
      'walletAmountAfterRedeem': 'Amount to be Added to Wallet:',
      'redeemNotesTitle': 'Important Notes:',
      'redeemNotes': '• Minimum points for redemption: {minPoints} points\n• {rate} points = 1 EGP\n• Amount will be added directly to your wallet\n• Points cannot be refunded after redemption',
      'pointsToEgpRate': '{rate} points = 1 EGP',
      'minRedeem': 'Minimum redemption: {minPoints} points',
      'availablePoints': 'Available points: {points}',
      'insufficientPointsMessage': 'You do not have enough points. Available points: {current}',
      'minRedeemMessage': 'Minimum points required for redemption is {minPoints} points',
      'howToRide': 'How to Ride',
      'language': 'Language',
      'selectLanguage': 'Select Language',
      'arabic': 'العربية',
      'english': 'English',
      'cancel': 'Cancel',
      'ok': 'OK',
      'save': 'Save',
      'loading': 'Loading...',
      'error': 'Error',
      'success': 'Success',
      'close': 'Close',
      'back': 'Back',
      'logout': 'Logout',
      'activeTrip': 'Active Trip',
      'startTrip': 'Start Trip',
      'endTrip': 'End Trip',
      'duration': 'Duration',
      'cost': 'Cost',
      'minutes': 'minutes',
      'egp': 'EGP',
      'noScootersAvailable': 'No scooters available in the nearby area',
      'errorLoadingScooters': 'Error loading scooters',
      'errorStartingTrip': 'Error starting trip',
      'help': 'Help',
      'howCanWeHelp': 'How can we help you?',
      'errorLoadingUserData': 'Error loading user data',
      'scanAndStartTrip': 'Scan and Start Trip',
      'scooter': 'Scooter',
      'available': 'Available',
      'unavailable': 'Unavailable',
      'locked': 'Locked',
      'unlocked': 'Unlocked',
      'battery': 'Battery',
      'km': 'km',
      'yourLocation': 'Your Location',
      'walletBalance': 'Wallet Balance',
      'availableBalance': 'Available Balance',
      'charge': 'Top Up',
      'history': 'History',
      'addCard': 'Add Card',
      'promoCode': 'Promo Code',
      'enterPromoCode': 'Please enter promo code',
      'errorLoadingWallet': 'Error loading wallet data',
      'cardSavedSuccessfully': 'Card saved successfully',
      'errorOccurred': 'An error occurred',
      'financialTransactions': 'Financial Transactions',
      'noTransactionsYet': 'No financial transactions yet',
      'transactionsWillAppearHere': 'All wallet-related financial transactions will appear here',
      'walletTopUp': 'Wallet Top Up',
      'tripPayment': 'Trip Payment',
      'refund': 'Refund',
      'adjustment': 'Adjustment',
      'subscription': 'Subscription',
      'transaction': 'Transaction',
      'pending': 'Pending',
      'failed': 'Failed',
      'activate': 'Activate',
      'addPromoCode': 'Add Promo Code',
      'promoCodeHint': 'Promo Code',
      'viewAndRedeemPoints': 'View and Redeem Points',
      'redeemLoyaltyBalance': 'Redeem loyalty points for wallet balance',
      'promoCodeActivated': 'Code activated successfully',
      'invalidPromoCode': 'Invalid code',
      'topUp': 'Top Up',
      'topUpBalance': 'Top Up Balance',
      'enterAmountToCharge': 'Enter Amount to Charge',
      'minimumChargeAmount': 'Minimum Charge: 1 EGP',
      'amountLabel': 'Amount (EGP)',
      'amountHint': '0.00',
      'pleaseEnterAmount': 'Please enter amount',
      'pleaseEnterValidNumber': 'Please enter a valid number',
      'minimumChargeRequired': 'Minimum charge is 1 EGP',
      'availablePaymentMethods': 'Available Payment Methods',
      'visaMastercard': 'Visa / Mastercard',
      'bankWallet': 'Bank Wallet',
      'pay': 'Pay',
      'paymentError': 'Error creating payment transaction',
      'enterAmount': 'Enter Amount',
      'pleaseEnterValidAmount': 'Please enter a valid amount',
      'minimumCharge': 'Minimum charge is 1 EGP',
      'confirmPayment': 'Confirm Payment',
      'paymentPageWillOpen': 'Payment page will open to charge your balance with',
      'continueText': 'Continue',
      'paymentPageOpened': 'Payment page opened. After completing payment, your balance will be updated automatically',
      'cannotOpenPaymentPage': 'Cannot open payment page',
      'transactionHistory': 'Transaction History',
      'errorLoadingTransactions': 'Error loading transactions',
      'addNewCard': 'Add New Card',
      'cardNumber': 'Card Number',
      'cardHolderName': 'Card Holder Name',
      'expiryDate': 'Expiry Date',
      'cvv': 'CVV',
      'setAsDefault': 'Set as Default Card',
      'referralCodeNotFound': 'Referral code not found. Please try again.',
      'errorLoadingData': 'Error loading data',
      'noReferralCodeAvailable': 'No referral code available. Please try again.',
      // Profile Screen
      'noData': 'No data available',
      'accountInfo': 'Account Information',
      'email': 'Email',
      'phoneNumber': 'Phone Number',
      'universityId': 'University ID',
      'age': 'Age',
      'notAvailable': 'Not Available',
      'year': 'year',
      'years': 'years',
      'changePassword': 'Change Password',
      'currentPassword': 'Current Password',
      'newPassword': 'New Password',
      'confirmNewPassword': 'Confirm New Password',
      'pleaseEnterCurrentPassword': 'Please enter current password',
      'pleaseEnterNewPassword': 'Please enter new password',
      'pleaseConfirmPassword': 'Please confirm password',
      'passwordMinLength': 'Password must be at least 8 characters',
      'passwordMismatch': 'Passwords do not match',
      'updatePassword': 'Update Password',
      'passwordUpdatedSuccessfully': 'Password updated successfully',
      'passwordUpdateError': 'Error updating password',
      'updateAvatar': 'Update Profile Picture',
      'avatarUpdatedSuccessfully': 'Profile picture updated successfully',
      'avatarUpdateError': 'Error updating profile picture',
      'pickImageError': 'Error selecting image',
      'dataLoadingError': 'Error loading data',
      'accountStatus': 'Account Status',
      'rejected': 'Rejected',
      'reviewNotes': 'Review Notes:',
      'resubmitNationalIdTitle': 'Resubmit National ID',
      'accountRejectedMessage': 'Your account has been rejected. Please upload national ID photos again for review.',
      'frontSide': 'Front Side',
      'backSide': 'Back Side',
      'uploadPhotos': 'Upload Photos',
      'pleaseUploadBothSides': 'Please upload national ID photos (front and back)',
      'nationalIdUploadedSuccessfully': 'National ID photos uploaded successfully',
      'nationalIdUploadError': 'Error uploading photos',
      'shareYourCode': 'Share Your Code',
      'errorSharing': 'Error sharing',
      'referralCode': 'Referral Code',
      'referredFriends': 'Referred Friends',
      'totalEarned': 'Total Earned',
      'getBalanceReward': 'Get',
      'balanceReward': 'EGP balance!',
      'referFriendsDescription': 'Refer up to',
      'friends': 'friends',
      'fromYourFriends': 'of your friends to get',
      'balanceWhenComplete': 'EGP balance when they complete their first trip!',
      'referralCodeLabel': 'Referral Code:',
      'codeUsed': 'Code Used',
      'receivedInFull': 'Received in Full',
      'myTrips': 'My Trips',
      'noTripsFound': 'No trips found',
      'tripDetails': 'Trip Details',
      'startTime': 'Start Time',
      'endTime': 'End Time',
      'status': 'Status',
      'paymentStatus': 'Payment Status',
      'baseCost': 'Base Cost',
      'discount': 'Discount',
      'penalty': 'Penalty',
      'zoneExitDetected': 'Zone Exit Detected',
      'completed': 'Completed',
      'active': 'Active',
      'cancelled': 'Cancelled',
      'paid': 'Paid',
      'partiallyPaid': 'Partially Paid',
      'unpaid': 'Unpaid',
      'noTripsYet': 'You have not taken any trips yet',
      'tripNumber': 'Trip #',
      'outsideZone': 'Outside Zone',
      'paidAmount': 'Paid',
      'remainingAmount': 'Remaining',
      'total': 'Total',
      'tapToViewDetails': 'Tap to view details',
      'fullyPaidMessage': 'Not fully paid - Please pay the remaining amount',
      'partiallyPaidMessage': 'Partially paid - Please pay the remaining amount',
      'errorLoadingTrips': 'Error loading trips',
      'tripInformation': 'Trip Information',
      'penaltyDetails': 'Penalty Details',
      'paymentDetails': 'Payment Details',
      'startPoint': 'Start Point',
      'endPoint': 'End Point',
      'notes': 'Notes',
      'penaltyDefault': 'Penalty',
      'penaltyDescription': 'Penalty Description:',
      'penaltyType': 'Type:',
      'appliedDate': 'Applied Date:',
      'zoneExitMessage': 'Zone exit from allowed area detected',
      'totalCost': 'Total Cost',
      'hours': 'hour',
      'durationFormat': '{hours} hour(s) {minutes} minute(s)',
      'minutesFormat': '{minutes} minute(s)',
      'hoursFormat': '{hours} hour(s)',
      'penaltyTypeZoneExit': 'Zone Exit',
      'penaltyTypeForbiddenParking': 'Forbidden Parking',
      'penaltyTypeUnlockedScooter': 'Unlocked Scooter',
      'penaltyTypeOther': 'Other',
      'penaltyStatusPending': 'Pending',
      'penaltyStatusWaived': 'Waived',
      'scanQRCode': 'Scan QR Code',
      'scanQRCodeToStart': 'Scan QR Code to Start',
      'positionCamera': 'Position your camera over the QR code',
      'tripDuration': 'Trip Duration',
      'closeTrip': 'Close Trip',
      'confirmCloseTrip': 'Are you sure you want to close the trip?',
      'costCalculationMessage': 'Cost will be calculated based on geographical zone',
      'tripClosedSuccessfully': 'Trip closed successfully',
      'backToHome': 'Back to Home',
      'tripCompletionError': 'Trip Completion Error',
      'tripCompletionErrorMessage': 'An error occurred while closing the trip:',
      'returnToHomeQuestion': 'Do you want to return to the home page?',
      'stayHere': 'Stay Here',
      'warning': 'Warning',
      'cannotCloseTripMessage': 'You cannot close this screen during the trip. Use the "Close Trip" button to complete the trip.',
      'cannotCancelTripMessage': 'You cannot cancel the trip from here. Please use the "End Trip" button to complete it.',
    },
  };
}

class _AppLocalizationsDelegate extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => ['ar', 'en'].contains(locale.languageCode);

  @override
  Future<AppLocalizations> load(Locale locale) async {
    return AppLocalizations(locale);
  }

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

