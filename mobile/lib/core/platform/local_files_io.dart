import 'dart:io';

import 'package:flutter/material.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

/// The phone and tablet implementation: real files in the app's own directory.

const bool supportsLocalFiles = true;

Future<String> packMediaDirectory(String packId) async {
  final base = await getApplicationSupportDirectory();
  final directory = Directory(p.join(base.path, 'packs', packId));

  if (!directory.existsSync()) {
    await directory.create(recursive: true);
  }

  return directory.path;
}

/// Write one downloaded media file and return where it landed.
Future<String> writeMediaFile(String directoryPath, String fileName, List<int> bytes) async {
  final file = File(p.join(directoryPath, fileName));
  await file.writeAsBytes(bytes);

  return file.path;
}

Future<void> deletePackMediaDirectory(String packId) async {
  final path = await packMediaDirectory(packId);
  final directory = Directory(path);

  if (directory.existsSync()) {
    await directory.delete(recursive: true);
  }
}

bool localFileExists(String path) => File(path).existsSync();

/// A picture that has already been downloaded.
Widget localFileImage(String path, {BoxFit fit = BoxFit.contain, double? height}) {
  return SizedBox(
    height: height,
    child: Image.file(
      File(path),
      fit: fit,
      errorBuilder: (_, _, _) => const SizedBox.shrink(),
    ),
  );
}
