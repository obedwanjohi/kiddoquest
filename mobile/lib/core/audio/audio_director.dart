import 'package:audio_session/audio_session.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:just_audio/just_audio.dart';

import '../db/local_database.dart';

/// Reads the app out loud.
///
/// A four-year-old cannot read the question. So every prompt is spoken: the
/// recorded narration when the pack carries one, and the device's own voice
/// when it does not. Today 202 of 538 questions have a recording — the ones
/// that do sound like a person, the rest still get read, and dropping in more
/// recordings later changes nothing above this class.
///
/// Everything here fails soft. A device with no voice installed, a browser that
/// blocks autoplay, a plugin missing in a test: none of that may stop a child
/// from playing, so every call is wrapped and the app carries on in silence.
class AudioDirector {
  AudioDirector({AudioPlayer? player, FlutterTts? tts})
      : _player = player ?? AudioPlayer(),
        _tts = tts ?? FlutterTts();

  final AudioPlayer _player;
  final FlutterTts _tts;

  bool _sessionReady = false;
  bool _muted = false;

  bool get isMuted => _muted;

  /// Silence everything, and stop whatever is playing right now.
  Future<void> setMuted(bool muted) async {
    _muted = muted;

    if (muted) await stop();
  }

  /// Ask the platform for an audio session that ducks other apps rather than
  /// stopping them, so a parent's music dips instead of dying.
  Future<void> _prepare() async {
    if (_sessionReady || kIsWeb) {
      _sessionReady = true;
      return;
    }

    try {
      final session = await AudioSession.instance;
      await session.configure(const AudioSessionConfiguration.speech());
      _sessionReady = true;
    } catch (_) {
      // No session is not a reason to be silent; playback usually still works.
      _sessionReady = true;
    }
  }

  /// Say a line, however it is best said.
  ///
  /// [media] is the resolved narration file if the pack has one. [text] is what
  /// to fall back to. Passing both is the normal case.
  Future<void> say({ResolvedMedia? media, String? text}) async {
    if (_muted) return;

    await _prepare();
    await stop();

    if (media != null && (media.hasFile || media.url.isNotEmpty)) {
      final played = await _playFile(media);
      if (played) return;
    }

    if (text != null && text.trim().isNotEmpty) {
      await _speak(text);
    }
  }

  /// Returns false when the file could not be played, so the caller can fall
  /// back to the device voice rather than leaving the child in silence.
  Future<bool> _playFile(ResolvedMedia media) async {
    try {
      if (media.hasFile && !kIsWeb) {
        await _player.setFilePath(media.localPath!);
      } else {
        await _player.setUrl(media.url);
      }

      await _player.play();

      return true;
    } catch (_) {
      return false;
    }
  }

  Future<void> _speak(String text) async {
    try {
      await _tts.setSpeechRate(0.45);
      await _tts.setPitch(1.1);
      await _tts.setVolume(1);
      await _tts.speak(text);
    } catch (_) {
      // A device with no voice pack installed. Nothing to do about it here.
    }
  }

  Future<void> stop() async {
    try {
      await _player.stop();
    } catch (_) {
      // Nothing was playing.
    }

    try {
      await _tts.stop();
    } catch (_) {
      // Nothing was being said.
    }
  }

  Future<void> dispose() async {
    await stop();

    try {
      await _player.dispose();
    } catch (_) {
      // Already gone.
    }
  }
}
