import 'package:flutter/material.dart';

/// The browser implementation: there is no local file store, and none is
/// needed. A pack keeps the URL of every picture and sound, and the browser's
/// own cache does the job the file store does on a device.
///
/// Nothing here throws. Callers check [supportsLocalFiles] and skip the work;
/// these bodies exist so that a caller which forgets to check still degrades
/// to "no cached file" rather than crashing a child's mission.

const bool supportsLocalFiles = false;

Future<String> packMediaDirectory(String packId) async => '';

Future<String> writeMediaFile(String directoryPath, String fileName, List<int> bytes) async => '';

Future<void> deletePackMediaDirectory(String packId) async {}

bool localFileExists(String path) => false;

Widget localFileImage(String path, {BoxFit fit = BoxFit.contain, double? height}) => const SizedBox.shrink();
