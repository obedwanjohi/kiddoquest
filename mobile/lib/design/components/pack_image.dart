import 'package:flutter/material.dart';

import '../../core/db/local_database.dart';
import '../../core/platform/local_files.dart';

/// Draws a picture that a content pack refers to.
///
/// Questions and options carry a media *key*, not a URL, so that one picture
/// used by twenty questions is stored once. This is what turns a key back into
/// something on screen: the downloaded file when there is one, the URL when
/// there is not, and nothing at all when neither works.
///
/// A missing picture must never stop a mission. The prompt text always carries
/// the question on its own, so every failure here falls back to empty space.
class PackImage extends StatelessWidget {
  const PackImage({
    super.key,
    required this.mediaKey,
    required this.media,
    this.height,
    this.fit = BoxFit.contain,
  });

  /// The key as it appears in the pack, for example `media/ab/abc123.webp`.
  final String? mediaKey;

  /// The pack's media table, from `ContentDao.mediaFor`.
  final Map<String, ResolvedMedia> media;

  final double? height;
  final BoxFit fit;

  @override
  Widget build(BuildContext context) {
    final key = mediaKey;

    if (key == null || key.isEmpty) return const SizedBox.shrink();

    final resolved = media[key];

    // A key with no entry is usually an emoji, or a reference the publisher
    // could not resolve. If it looks like a URL, try it anyway.
    if (resolved == null) {
      return key.startsWith('http') ? _network(key) : const SizedBox.shrink();
    }

    if (supportsLocalFiles && resolved.hasFile && localFileExists(resolved.localPath!)) {
      return localFileImage(resolved.localPath!, fit: fit, height: height);
    }

    if (resolved.url.isEmpty) return const SizedBox.shrink();

    return _network(resolved.url);
  }

  Widget _network(String url) => SizedBox(
        height: height,
        child: Image.network(
          url,
          fit: fit,
          errorBuilder: (_, _, _) => const SizedBox.shrink(),
        ),
      );
}
