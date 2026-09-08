import 'package:speech_to_text/speech_to_text.dart';

/// Hears a child say a word.
///
/// Recognition is an extra here, never a gate. A great many of the devices this
/// product is for have no language pack and no microphone worth the name — a
/// television box, a cheap phone — and a four-year-old cannot be told to buy
/// better hardware. So the renderer asks [isAvailable] first and falls back to
/// practice mode, and even when recognition works a word it fails to catch is
/// still a word the child said: what recognition buys is the delight of being
/// heard, not a mark.
class SpeechListener {
  SpeechListener([SpeechToText? speech]) : _speech = speech ?? SpeechToText();

  final SpeechToText _speech;

  bool? _available;

  bool get isListening => _speech.isListening;

  /// Whether this device can hear at all. Asked once and remembered, because
  /// the answer cannot change while the app is open and the check itself puts a
  /// permission prompt on the screen.
  Future<bool> isAvailable() async {
    if (_available != null) return _available!;

    try {
      return _available = await _speech.initialize(
        onError: (_) {},
        onStatus: (_) {},
      );
    } catch (_) {
      return _available = false;
    }
  }

  /// Listen for a few seconds and report what was heard.
  ///
  /// [onHeard] is called with the final transcript, or an empty string when
  /// nothing was made out.
  Future<void> listen({
    required void Function(String heard) onHeard,
    Duration listenFor = const Duration(seconds: 5),
  }) async {
    if (!await isAvailable()) {
      onHeard('');
      return;
    }

    try {
      await _speech.listen(
        listenOptions: SpeechListenOptions(listenFor: listenFor),
        onResult: (result) {
          if (result.finalResult) {
            onHeard(result.recognizedWords);
          }
        },
      );
    } catch (_) {
      onHeard('');
    }
  }

  Future<void> stop() async {
    try {
      await _speech.stop();
    } catch (_) {
      // Nothing was listening.
    }
  }

  /// Did the child say the word?
  ///
  /// Generous on purpose. Recognition mishears small children constantly, and
  /// the cost of being too strict — telling a child who said it right that they
  /// did not — is far worse than the cost of being too kind.
  static bool matches(String heard, String word) {
    final said = _simplify(heard);
    final target = _simplify(word);

    if (said.isEmpty || target.isEmpty) return false;

    return said.contains(target) || target.contains(said);
  }

  static String _simplify(String value) =>
      value.toLowerCase().replaceAll(RegExp('[^a-z0-9 ]'), '').trim();
}
