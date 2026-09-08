/// The two pieces of content that are not missions: the daily devotional and
/// the songs hub.
///
/// Both are reached exactly when the connection is worst — a devotional at
/// bedtime, a song in a car — so the whole list is cached and today's is picked
/// on the device rather than asked for.
class Extras {
  const Extras({
    this.devotionals = const [],
    this.songs = const [],
    this.devotionalEnabled = true,
    this.songsEnabled = true,
  });

  final List<Devotional> devotionals;
  final List<Song> songs;
  final bool devotionalEnabled;
  final bool songsEnabled;

  /// Today's devotional, by the same day-of-year rule the server uses, so the
  /// app and the website show a family the same verse on the same day.
  Devotional? devotionalFor(DateTime day) {
    if (devotionals.isEmpty) return null;

    final startOfYear = DateTime(day.year);
    final dayOfYear = day.difference(startOfYear).inDays;

    return devotionals[dayOfYear % devotionals.length];
  }

  factory Extras.fromJson(Map<String, dynamic> json) {
    final devotional = (json['devotional'] as Map?)?.cast<String, dynamic>() ?? const {};
    final songs = (json['songs'] as Map?)?.cast<String, dynamic>() ?? const {};

    return Extras(
      devotionalEnabled: devotional['enabled'] as bool? ?? true,
      songsEnabled: songs['enabled'] as bool? ?? true,
      devotionals: ((devotional['items'] as List?) ?? const [])
          .whereType<Map>()
          .map((e) => Devotional.fromJson(e.cast<String, dynamic>()))
          .toList(),
      songs: ((songs['items'] as List?) ?? const [])
          .whereType<Map>()
          .map((e) => Song.fromJson(e.cast<String, dynamic>()))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'devotional': {
          'enabled': devotionalEnabled,
          'items': devotionals.map((d) => d.toJson()).toList(),
        },
        'songs': {
          'enabled': songsEnabled,
          'items': songs.map((s) => s.toJson()).toList(),
        },
      };
}

class Devotional {
  const Devotional({
    required this.verseText,
    required this.verseRef,
    this.teaching = '',
    this.prayer = '',
    this.emoji = '📖',
  });

  final String verseText;
  final String verseRef;
  final String teaching;
  final String prayer;
  final String emoji;

  /// What the app reads out loud, in the order a child hears it.
  String get spoken => '$verseText. From $verseRef. $teaching Let us pray. $prayer';

  factory Devotional.fromJson(Map<String, dynamic> json) => Devotional(
        verseText: json['verse_text'] as String? ?? '',
        verseRef: json['verse_ref'] as String? ?? '',
        teaching: json['teaching'] as String? ?? '',
        prayer: json['prayer'] as String? ?? '',
        emoji: json['emoji'] as String? ?? '📖',
      );

  Map<String, dynamic> toJson() => {
        'verse_text': verseText,
        'verse_ref': verseRef,
        'teaching': teaching,
        'prayer': prayer,
        'emoji': emoji,
      };
}

/// A song in the hub.
///
/// [audio] is a pack media key and is null until KiddoQuest licenses its own
/// recordings; [link] is the video that stands in meanwhile. The distinction
/// matters to a child on a matatu: one plays, the other needs the internet, and
/// the hub says which.
class Song {
  const Song({
    required this.id,
    required this.title,
    this.category = '',
    this.emoji = '🎵',
    this.palette = 'castle',
    this.audio,
    this.link,
  });

  final int id;
  final String title;
  final String category;
  final String emoji;
  final String palette;
  final String? audio;
  final String? link;

  bool get playsOffline => audio != null && audio!.isNotEmpty;

  bool get needsInternet => !playsOffline && (link != null && link!.isNotEmpty);

  factory Song.fromJson(Map<String, dynamic> json) => Song(
        id: (json['id'] as num?)?.toInt() ?? 0,
        title: json['title'] as String? ?? 'A song',
        category: json['category'] as String? ?? '',
        emoji: json['emoji'] as String? ?? '🎵',
        palette: json['palette'] as String? ?? 'castle',
        audio: json['audio'] as String?,
        link: json['link'] as String?,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'category': category,
        'emoji': emoji,
        'palette': palette,
        'audio': audio,
        'link': link,
      };
}
