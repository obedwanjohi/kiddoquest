/// The content pack: one adventure world at one version, everything a child
/// needs to play it with no network at all.
///
/// The shape is defined by ContentPackBuilder on the server and by appendix C
/// of the plan. Both sides are checked by the same fixtures.
library;

class PackSummary {
  const PackSummary({
    required this.packId,
    required this.version,
    required this.name,
    this.worldId,
    this.worldSlug,
    this.icon,
    this.themeColor,
    this.level,
    this.subjectCode,
    this.isFree = false,
    this.sortOrder = 0,
    this.missions = 0,
    this.questions = 0,
    this.bytesCore = 0,
    this.bytesVideo = 0,
    this.sha256,
    this.url,
  });

  final String packId;
  final int version;
  final String name;
  final int? worldId;
  final String? worldSlug;
  final String? icon;
  final String? themeColor;
  final String? level;
  final String? subjectCode;
  final bool isFree;
  final int sortOrder;
  final int missions;
  final int questions;
  final int bytesCore;
  final int bytesVideo;
  final String? sha256;
  final String? url;

  /// What the download will cost in megabytes, rounded the way a parent thinks
  /// about their bundle.
  double get megabytesCore => bytesCore / 1048576;

  factory PackSummary.fromJson(Map<String, dynamic> json) => PackSummary(
        packId: json['pack_id'] as String,
        version: (json['version'] as num?)?.toInt() ?? 1,
        name: json['name'] as String? ?? json['pack_id'] as String,
        worldId: (json['world_id'] as num?)?.toInt(),
        worldSlug: json['world_slug'] as String?,
        icon: json['icon'] as String?,
        themeColor: json['theme_color'] as String?,
        level: json['level'] as String?,
        subjectCode: json['subject_code'] as String?,
        isFree: json['is_free'] as bool? ?? false,
        sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
        missions: (json['missions'] as num?)?.toInt() ?? 0,
        questions: (json['questions'] as num?)?.toInt() ?? 0,
        bytesCore: (json['bytes_core'] as num?)?.toInt() ?? 0,
        bytesVideo: (json['bytes_video'] as num?)?.toInt() ?? 0,
        sha256: json['sha256'] as String?,
        url: json['url'] as String?,
      );

  Map<String, dynamic> toJson() => {
        'pack_id': packId,
        'version': version,
        'name': name,
        'world_id': worldId,
        'world_slug': worldSlug,
        'icon': icon,
        'theme_color': themeColor,
        'level': level,
        'subject_code': subjectCode,
        'is_free': isFree,
        'sort_order': sortOrder,
        'missions': missions,
        'questions': questions,
        'bytes_core': bytesCore,
        'bytes_video': bytesVideo,
        'sha256': sha256,
        'url': url,
      };
}

class ContentPack {
  const ContentPack({
    required this.packId,
    required this.version,
    required this.world,
    required this.missions,
    this.level,
    this.subjectCode,
    this.subjectName,
    this.subjectCategory,
    this.media = const [],
  });

  final String packId;
  final int version;
  final PackWorld world;
  final List<PackMission> missions;
  final String? level;
  final String? subjectCode;
  final String? subjectName;
  final String? subjectCategory;
  final List<MediaEntry> media;

  factory ContentPack.fromJson(Map<String, dynamic> json) {
    final subject = (json['subject'] as Map?)?.cast<String, dynamic>() ?? const {};

    return ContentPack(
      packId: json['pack_id'] as String,
      version: (json['version'] as num?)?.toInt() ?? 1,
      level: json['level'] as String?,
      subjectCode: subject['code'] as String?,
      subjectName: subject['name'] as String?,
      subjectCategory: subject['category'] as String?,
      world: PackWorld.fromJson((json['world'] as Map).cast<String, dynamic>()),
      missions: ((json['missions'] as List?) ?? const [])
          .map((e) => PackMission.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
      media: ((json['media'] as List?) ?? const [])
          .map((e) => MediaEntry.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
    );
  }
}

class PackWorld {
  const PackWorld({
    required this.id,
    required this.slug,
    required this.name,
    this.icon,
    this.description,
    this.themeColor,
    this.isFree = false,
    this.sortOrder = 0,
  });

  final int id;
  final String slug;
  final String name;
  final String? icon;
  final String? description;
  final String? themeColor;
  final bool isFree;
  final int sortOrder;

  factory PackWorld.fromJson(Map<String, dynamic> json) => PackWorld(
        id: (json['id'] as num).toInt(),
        slug: json['slug'] as String? ?? '',
        name: json['name'] as String? ?? '',
        icon: json['icon'] as String?,
        description: json['description'] as String?,
        themeColor: json['theme_color'] as String?,
        isFree: json['is_free'] as bool? ?? false,
        sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      );
}

class PackMission {
  const PackMission({
    required this.id,
    required this.title,
    this.slug,
    this.displayTitle,
    this.description,
    this.sortOrder = 0,
    this.questionsPerSession = 6,
    this.passThresholdPercent = 60,
    this.starsReward = 3,
    this.estimatedMinutes = 5,
    this.introText,
    this.introAudio,
    this.outroText,
    this.videoPath,
    this.questions = const [],
  });

  final int id;
  final String title;
  final String? slug;
  final String? displayTitle;
  final String? description;
  final int sortOrder;
  final int questionsPerSession;
  final int passThresholdPercent;
  final int starsReward;
  final int estimatedMinutes;
  final String? introText;
  final String? introAudio;
  final String? outroText;
  final String? videoPath;
  final List<PackQuestion> questions;

  String get label => displayTitle?.isNotEmpty == true ? displayTitle! : title;

  factory PackMission.fromJson(Map<String, dynamic> json) {
    final intro = (json['intro'] as Map?)?.cast<String, dynamic>() ?? const {};
    final outro = (json['outro'] as Map?)?.cast<String, dynamic>() ?? const {};
    final video = (json['video'] as Map?)?.cast<String, dynamic>();

    return PackMission(
      id: (json['id'] as num).toInt(),
      title: json['title'] as String? ?? 'Mission',
      slug: json['slug'] as String?,
      displayTitle: json['display_title'] as String?,
      description: json['description'] as String?,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      questionsPerSession: (json['questions_per_session'] as num?)?.toInt() ?? 6,
      passThresholdPercent: (json['pass_threshold_percent'] as num?)?.toInt() ?? 60,
      starsReward: (json['stars_reward'] as num?)?.toInt() ?? 3,
      estimatedMinutes: (json['estimated_minutes'] as num?)?.toInt() ?? 5,
      introText: intro['text'] as String?,
      introAudio: intro['audio'] as String?,
      outroText: outro['text'] as String?,
      videoPath: video?['path'] as String?,
      questions: ((json['questions'] as List?) ?? const [])
          .map((e) => PackQuestion.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
    );
  }
}

class PackQuestion {
  const PackQuestion({
    required this.id,
    required this.type,
    this.bankId,
    this.prompt,
    this.narrationText,
    this.narrationAudio,
    this.image,
    this.audio,
    this.hint,
    this.explanation,
    this.points = 1,
    this.difficulty,
    this.scoringConfig = const {},
    this.metadata = const {},
    this.options = const [],
  });

  final int id;
  final String type;
  final int? bankId;
  final String? prompt;
  final String? narrationText;
  final String? narrationAudio;
  final String? image;
  final String? audio;
  final String? hint;
  final String? explanation;
  final int points;
  final String? difficulty;
  final Map<String, dynamic> scoringConfig;
  final Map<String, dynamic> metadata;
  final List<PackOption> options;

  factory PackQuestion.fromJson(Map<String, dynamic> json) {
    final narration = (json['narration'] as Map?)?.cast<String, dynamic>() ?? const {};

    return PackQuestion(
      id: (json['id'] as num).toInt(),
      type: json['type'] as String? ?? 'multiple_choice',
      bankId: (json['bank_id'] as num?)?.toInt(),
      prompt: json['prompt'] as String?,
      narrationText: narration['text'] as String?,
      narrationAudio: narration['audio'] as String?,
      image: json['image'] as String?,
      audio: json['audio'] as String?,
      hint: json['hint'] as String?,
      explanation: json['explanation'] as String?,
      points: (json['points'] as num?)?.toInt() ?? 1,
      difficulty: json['difficulty'] as String?,
      scoringConfig: _asMap(json['scoring_config']),
      metadata: _asMap(json['metadata']),
      options: ((json['options'] as List?) ?? const [])
          .map((e) => PackOption.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
    );
  }

  Map<String, dynamic> toScorerJson() => {
        'id': id,
        'type': type,
        'scoring_config': scoringConfig,
        'metadata': metadata,
        'options': options.map((o) => o.toScorerJson()).toList(),
      };

  static Map<String, dynamic> _asMap(dynamic value) {
    if (value is Map) return value.cast<String, dynamic>();
    return const {};
  }
}

class PackOption {
  const PackOption({
    required this.id,
    this.text,
    this.image,
    this.audio,
    this.isCorrect = false,
    this.contentType,
    this.matchKey,
    this.sortOrder = 0,
  });

  final int id;
  final String? text;
  final String? image;
  final String? audio;
  final bool isCorrect;
  final String? contentType;
  final String? matchKey;
  final int sortOrder;

  factory PackOption.fromJson(Map<String, dynamic> json) => PackOption(
        id: (json['id'] as num).toInt(),
        text: json['text'] as String?,
        image: json['image'] as String?,
        audio: json['audio'] as String?,
        isCorrect: json['is_correct'] as bool? ?? false,
        contentType: json['content_type'] as String?,
        matchKey: json['match_key'] as String?,
        sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      );

  Map<String, dynamic> toScorerJson() => {
        'id': id,
        'is_correct': isCorrect,
        'match_key': matchKey,
        'sort_order': sortOrder,
      };
}

/// One downloadable file the pack refers to.
class MediaEntry {
  const MediaEntry({
    required this.path,
    required this.url,
    this.sha256,
    this.bytes = 0,
    this.kind = 'image',
    this.required = true,
  });

  final String path;
  final String url;
  final String? sha256;
  final int bytes;
  final String kind;
  final bool required;

  bool get isVideo => kind == 'video';

  factory MediaEntry.fromJson(Map<String, dynamic> json) => MediaEntry(
        path: json['path'] as String,
        url: json['url'] as String? ?? '',
        sha256: json['sha256'] as String?,
        bytes: (json['bytes'] as num?)?.toInt() ?? 0,
        kind: json['kind'] as String? ?? 'image',
        required: json['required'] as bool? ?? true,
      );
}
