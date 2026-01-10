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
  String get freeBalance => _localizedValues[locale.languageCode]?['freeBalance'] ?? 'رصيد مجاني';
  String get chargeBalance => _localizedValues[locale.languageCode]?['chargeBalance'] ?? 'اشحن رصيدك';
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
  String get charge => _localizedValues[locale.languageCode]?['charge'] ?? 'اشحن';
  String get history => _localizedValues[locale.languageCode]?['history'] ?? 'تاريخ';
  String get addCard => _localizedValues[locale.languageCode]?['addCard'] ?? 'اضف كارت';
  String get promoCode => _localizedValues[locale.languageCode]?['promoCode'] ?? 'كود البرومو';
  String get enterPromoCode => _localizedValues[locale.languageCode]?['enterPromoCode'] ?? 'يرجى إدخال كود البرومو';
  String get errorLoadingWallet => _localizedValues[locale.languageCode]?['errorLoadingWallet'] ?? 'حدث خطأ في تحميل بيانات المحفظة';
  String get cardSavedSuccessfully => _localizedValues[locale.languageCode]?['cardSavedSuccessfully'] ?? 'تم حفظ الكارت بنجاح';
  String get errorOccurred => _localizedValues[locale.languageCode]?['errorOccurred'] ?? 'حدث خطأ';
  
  // Top Up Screen
  String get topUp => _localizedValues[locale.languageCode]?['topUp'] ?? 'شحن الرصيد';
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
  String get bronze => _localizedValues[locale.languageCode]?['bronze'] ?? 'برونزي';
  String get silver => _localizedValues[locale.languageCode]?['silver'] ?? 'فضي';
  String get gold => _localizedValues[locale.languageCode]?['gold'] ?? 'ذهبي';
  
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
      'freeBalance': 'رصيد مجاني',
      'chargeBalance': 'اشحن رصيدك',
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
      'charge': 'اشحن',
      'history': 'تاريخ',
      'addCard': 'اضف كارت',
      'promoCode': 'كود البرومو',
      'enterPromoCode': 'يرجى إدخال كود البرومو',
      'errorLoadingWallet': 'حدث خطأ في تحميل بيانات المحفظة',
      'cardSavedSuccessfully': 'تم حفظ الكارت بنجاح',
      'errorOccurred': 'حدث خطأ',
      'topUp': 'شحن الرصيد',
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
      'freeBalance': 'Free Balance',
      'chargeBalance': 'Charge Balance',
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
      'balanceAfter': 'Balance after',
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
      'charge': 'Charge',
      'history': 'History',
      'addCard': 'Add Card',
      'promoCode': 'Promo Code',
      'enterPromoCode': 'Please enter promo code',
      'errorLoadingWallet': 'Error loading wallet data',
      'cardSavedSuccessfully': 'Card saved successfully',
      'errorOccurred': 'An error occurred',
      'topUp': 'Top Up',
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

