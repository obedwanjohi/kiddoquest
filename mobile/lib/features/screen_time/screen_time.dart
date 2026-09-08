import 'dart:math' as math;

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';

/// How much of today's learning time a child has used.
///
/// Two sources have to agree: what this device has watched them play, and what
/// the server knows from every device. The larger wins. A child who played on
/// the tablet this morning should not get a fresh hour on the phone this
/// afternoon, and a child whose sync has not caught up should not lose time
/// they have not spent.
class ScreenTime {
  const ScreenTime({required this.limitMinutes, required this.usedSeconds});

  final int limitMinutes;
  final int usedSeconds;

  bool get unlimited => limitMinutes <= 0;

  int get usedMinutes => (usedSeconds / 60).round();

  int get minutesLeft => unlimited ? 0 : math.max(0, limitMinutes - usedMinutes);

  bool get isOver => !unlimited && minutesLeft <= 0;

  /// True once the end is close enough to warn about but not yet reached.
  bool get isNearlyOver => !unlimited && minutesLeft > 0 && minutesLeft <= 5;
}

final screenTimeProvider = FutureProvider.family<ScreenTime, Child>((ref, child) async {
  final onThisDevice = await ref.watch(progressDaoProvider).secondsPlayedToday(child.id);

  return ScreenTime(
    limitMinutes: child.dailyTimeLimitMinutes,
    usedSeconds: math.max(onThisDevice, child.todayPlayedSeconds),
  );
});
