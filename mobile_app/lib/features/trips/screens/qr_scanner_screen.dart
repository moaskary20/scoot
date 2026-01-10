import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import 'dart:ui' as ui;
import '../../../core/constants/app_constants.dart';
import '../../../core/l10n/app_localizations.dart';
import '../../../core/services/language_service.dart';

class QRScannerScreen extends StatefulWidget {
  final Function(String) onQRCodeScanned;

  const QRScannerScreen({
    super.key,
    required this.onQRCodeScanned,
  });

  @override
  State<QRScannerScreen> createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  MobileScannerController? _controller;
  bool _isProcessing = false;
  bool _hasError = false;
  String? _errorMessage;
  final TextEditingController _manualInputController = TextEditingController();
  final FocusNode _manualInputFocusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _initializeScanner();
  }

  Future<void> _initializeScanner() async {
    try {
      _controller = MobileScannerController(
        detectionSpeed: DetectionSpeed.noDuplicates,
        facing: CameraFacing.back,
      );
      setState(() {
        _hasError = false;
      });
    } catch (e) {
      setState(() {
        _hasError = true;
        final loc = AppLocalizations.of(context);
        _errorMessage = '${loc?.failedToOpenCamera ?? 'فشل في فتح الكاميرا'}: $e';
      });
    }
  }

  @override
  void dispose() {
    // Properly stop and dispose camera controller
    if (_controller != null) {
      try {
        _controller!.stop();
      } catch (e) {
        print('⚠️ Error stopping camera: $e');
      }
      try {
        _controller!.dispose();
      } catch (e) {
        print('⚠️ Error disposing camera: $e');
      }
      _controller = null;
    }
    _manualInputController.dispose();
    _manualInputFocusNode.dispose();
    super.dispose();
  }

  void _handleManualInput() async {
    final qrCode = _manualInputController.text.trim();
    if (qrCode.isEmpty) {
      final loc = AppLocalizations.of(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(loc?.pleaseEnterQRCode ?? 'يرجى إدخال QR Code'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isProcessing = true;
    });

    // Stop camera if it's running
    if (_controller != null) {
      try {
        await _controller!.stop();
      } catch (e) {
        print('⚠️ Error stopping scanner: $e');
      }
      try {
        await _controller!.dispose();
      } catch (e) {
        print('⚠️ Error disposing scanner: $e');
      }
      _controller = null;
    }

    // Return QR code
    widget.onQRCodeScanned(qrCode);
    
    // Close screen after a short delay
    if (mounted) {
      await Future.delayed(const Duration(milliseconds: 100));
      if (mounted) {
        Navigator.pop(context);
      }
    }
  }

  void _handleBarcode(BarcodeCapture barcodeCapture) async {
    if (_isProcessing || _controller == null) return;

    final barcodes = barcodeCapture.barcodes;
    if (barcodes.isEmpty) return;

    final qrCode = barcodes.first.rawValue;
    if (qrCode == null || qrCode.isEmpty) return;

    setState(() {
      _isProcessing = true;
    });

    // Stop and dispose scanner properly
    if (_controller != null) {
      try {
        await _controller!.stop();
      } catch (e) {
        print('⚠️ Error stopping scanner: $e');
      }
      try {
        await _controller!.dispose();
      } catch (e) {
        print('⚠️ Error disposing scanner: $e');
      }
      _controller = null;
    }

    // Return QR code
    widget.onQRCodeScanned(qrCode);
    
    // Close screen after a short delay to ensure camera is closed
    if (mounted) {
      await Future.delayed(const Duration(milliseconds: 100));
      if (mounted) {
        Navigator.pop(context);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final languageService = Provider.of<LanguageService>(context, listen: false);
    final localizations = AppLocalizations.of(context);
    final isArabic = languageService.isArabic;
    return Directionality(
      textDirection: isArabic ? ui.TextDirection.rtl : ui.TextDirection.ltr,
      child: Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.white),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text(
            localizations?.scanQRCode ?? 'مسح QR Code',
            style: const TextStyle(color: Colors.white),
          ),
        ),
        body: Stack(
          children: [
            // Camera view
            if (_controller != null && !_hasError)
              MobileScanner(
                controller: _controller!,
                onDetect: _handleBarcode,
                errorBuilder: (context, error, child) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          color: Colors.red,
                          size: 64,
                        ),
                        const SizedBox(height: 16),
                        Builder(
                          builder: (context) {
                            final loc = AppLocalizations.of(context);
                            return Text(
                              '${loc?.cameraError ?? 'خطأ في الكاميرا'}: ${error.toString()}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                              ),
                              textAlign: TextAlign.center,
                            );
                          },
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton(
                          onPressed: () {
                            Navigator.pop(context);
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Color(AppConstants.primaryColor),
                          ),
                          child: Text(AppLocalizations.of(context)?.close ?? 'إغلاق'),
                        ),
                      ],
                    ),
                  );
                },
              )
            else if (_hasError)
              Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.error_outline,
                      color: Colors.red,
                      size: 64,
                    ),
                    const SizedBox(height: 16),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 40),
                      child: Builder(
                        builder: (context) {
                          final loc = AppLocalizations.of(context);
                          return Text(
                            _errorMessage ?? (loc?.errorOpeningCamera ?? 'حدث خطأ في فتح الكاميرا'),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                            ),
                            textAlign: TextAlign.center,
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Color(AppConstants.primaryColor),
                      ),
                      child: Text(localizations?.close ?? 'إغلاق'),
                    ),
                  ],
                ),
              )
            else
              const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    CircularProgressIndicator(
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                    SizedBox(height: 16),
                    Builder(
                      builder: (context) {
                        final loc = AppLocalizations.of(context);
                        return Text(
                          loc?.openingCamera ?? 'جاري فتح الكاميرا...',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            
            // Overlay
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(0.7),
                    Colors.transparent,
                    Colors.transparent,
                    Colors.black.withOpacity(0.7),
                  ],
                  stops: const [0.0, 0.3, 0.7, 1.0],
                ),
              ),
            ),
            
            // Scanning frame
            Center(
              child: Container(
                width: 250,
                height: 250,
                decoration: BoxDecoration(
                  border: Border.all(
                    color: Color(AppConstants.primaryColor),
                    width: 3,
                  ),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Stack(
                  children: [
                    // Corner indicators
                    Positioned(
                      top: 0,
                      left: 0,
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          border: Border(
                            top: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                            left: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                          ),
                          borderRadius: const BorderRadius.only(
                            topLeft: Radius.circular(20),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      top: 0,
                      right: 0,
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          border: Border(
                            top: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                            right: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                          ),
                          borderRadius: const BorderRadius.only(
                            topRight: Radius.circular(20),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      left: 0,
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          border: Border(
                            bottom: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                            left: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                          ),
                          borderRadius: const BorderRadius.only(
                            bottomLeft: Radius.circular(20),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          border: Border(
                            bottom: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                            right: BorderSide(
                              color: Color(AppConstants.primaryColor),
                              width: 4,
                            ),
                          ),
                          borderRadius: const BorderRadius.only(
                            bottomRight: Radius.circular(20),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            // Instructions
            Positioned(
              bottom: 180,
              left: 0,
              right: 0,
              child: Column(
                children: [
                  Icon(
                    Icons.qr_code_scanner,
                    color: Color(AppConstants.primaryColor),
                    size: 48,
                  ),
                  const SizedBox(height: 16),
                  Builder(
                    builder: (context) {
                      final loc = AppLocalizations.of(context);
                      return Text(
                        loc?.placeQRCodeInFrame ?? 'ضع QR Code داخل الإطار',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      );
                    },
                  ),
                  const SizedBox(height: 8),
                  Builder(
                    builder: (context) {
                      final loc = AppLocalizations.of(context);
                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 40),
                        child: Text(
                          loc?.codeWillBeScannedAutomatically ?? 'سيتم مسح الكود تلقائياً',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            
            // Manual Input Section
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.9),
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      textDirection: TextDirection.rtl,
                      children: [
                        Icon(
                          Icons.edit,
                          color: Color(AppConstants.primaryColor),
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Builder(
                          builder: (context) {
                            final loc = AppLocalizations.of(context);
                            return Text(
                              loc?.orEnterQRCodeManually ?? 'أو أدخل QR Code يدوياً',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      textDirection: TextDirection.rtl,
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _manualInputController,
                            focusNode: _manualInputFocusNode,
                            textDirection: TextDirection.ltr,
                            textAlign: TextAlign.left,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                            ),
                            decoration: InputDecoration(
                              hintText: localizations?.enterQRCodeHere ?? 'أدخل QR Code هنا',
                              hintStyle: TextStyle(
                                color: Colors.white.withOpacity(0.5),
                                fontSize: 14,
                              ),
                              filled: true,
                              fillColor: Colors.grey[900]?.withOpacity(0.7),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Colors.grey[700]!,
                                  width: 1,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Colors.grey[700]!,
                                  width: 1,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Color(AppConstants.primaryColor),
                                  width: 2,
                                ),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 14,
                              ),
                            ),
                            onSubmitted: (_) => _handleManualInput(),
                          ),
                        ),
                        const SizedBox(width: 12),
                        ElevatedButton(
                          onPressed: _isProcessing ? null : _handleManualInput,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Color(AppConstants.primaryColor),
                            foregroundColor: Color(AppConstants.secondaryColor),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 20,
                              vertical: 14,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            minimumSize: const Size(80, 48),
                          ),
                          child: _isProcessing
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white,
                                    ),
                                  ),
                                )
                              : Builder(
                                  builder: (context) {
                                    final loc = AppLocalizations.of(context);
                                    return Text(
                                      loc?.done ?? 'تم',
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    );
                                  },
                                ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            
            // Processing indicator
            if (_isProcessing)
              Container(
                color: Colors.black.withOpacity(0.7),
                child: const Center(
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

