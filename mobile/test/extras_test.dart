import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/audio/speech_listener.dart';
import 'package:kiddoquest/core/models/extras.dart';

void main() {
  group('Extras', () {
    final extras = Extras.fromJson({
      'devotional': {
        'enabled': true,
        'items': [
          {'verse_text': 'A', 'verse_ref': 'One 1:1', 'teaching': 'T', 'prayer': 'P', 'emoji': '🦁'},
          {'verse_text': 'B', 'verse_ref': 'Two 2:2'},
          {'verse_text': 'C', 'verse_ref': 'Three 3:3'},
        ],
      },
      'songs': {
        'enabled': true,
        'items': [
          {'id': 1, 'title': 'Alphabet', 'audio': 'audio/songs/abc.mp3'},
          {'id': 2, 'title': 'Counting', 'link': 'https://example.test/watch'},
        ],
      },
    });

    test('picks the same devotional the server would pick', () {
      // Day of year 0 → the first entry, and it wraps by the same rule.
      expect(extras.devotionalFor(DateTime(2026))!.verseRef, 'One 1:1');
      expect(extras.devotionalFor(DateTime(2026, 1, 2))!.verseRef, 'Two 2:2');
      expect(extras.devotionalFor(DateTime(2026, 1, 4))!.verseRef, 'One 1:1');
    });

    test('the same day gives the same verse however often it is asked', () {
      final first = extras.devotionalFor(DateTime(2026, 5, 17))!.verseRef;
      final again = extras.devotionalFor(DateTime(2026, 5, 17, 23, 59))!.verseRef;

      expect(first, again);
    });

    test('reads out the verse, the reference, the teaching and the prayer', () {
      final devotional = extras.devotionals.first;

      expect(devotional.spoken, contains('A'));
      expect(devotional.spoken, contains('One 1:1'));
      expect(devotional.spoken, contains('P'));
    });

    test('a song with a recording plays offline; a song with only a link does not', () {
      expect(extras.songs.first.playsOffline, isTrue);
      expect(extras.songs.first.needsInternet, isFalse);

      expect(extras.songs.last.playsOffline, isFalse);
      expect(extras.songs.last.needsInternet, isTrue);
    });

    test('survives a reply with nothing in it', () {
      final empty = Extras.fromJson(const {});

      expect(empty.devotionals, isEmpty);
      expect(empty.devotionalFor(DateTime.now()), isNull);
      expect(empty.songs, isEmpty);
      // Absent is not the same as switched off; the server decides that.
      expect(empty.devotionalEnabled, isTrue);
    });

    test('round-trips through the cache', () {
      final again = Extras.fromJson(extras.toJson());

      expect(again.devotionals.length, 3);
      expect(again.songs.first.audio, 'audio/songs/abc.mp3');
      expect(again.songsEnabled, isTrue);
    });
  });

  group('hearing a child say a word', () {
    test('accepts the word said plainly', () {
      expect(SpeechListener.matches('elephant', 'elephant'), isTrue);
    });

    test('is generous about case, punctuation and a sentence around it', () {
      expect(SpeechListener.matches('Elephant!', 'elephant'), isTrue);
      expect(SpeechListener.matches('it is an elephant', 'elephant'), isTrue);
    });

    test('does not accept silence', () {
      expect(SpeechListener.matches('', 'elephant'), isFalse);
      expect(SpeechListener.matches('   ', 'elephant'), isFalse);
    });

    test('does not accept a different word', () {
      expect(SpeechListener.matches('giraffe', 'elephant'), isFalse);
    });
  });
}
