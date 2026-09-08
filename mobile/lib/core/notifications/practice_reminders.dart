import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest_all.dart' as tzdata;
import 'package:timezone/timezone.dart' as tz;

/// The nudge that brings a family back tomorrow.
///
/// Scheduled on the device rather than pushed from a server, deliberately. A
/// reminder to practise does not need to know anything a server knows, and a
/// family whose data bundle ran out on Tuesday is exactly the family the
/// reminder is for. It arrives with the aeroplane mode on.
///
/// Server-sent notifications — a parent's weekly report, a message about a
/// payment — are a different thing and still need Firebase. The device token
/// endpoint is already built and waiting for it.
class PracticeReminders {
  PracticeReminders({FlutterLocalNotificationsPlugin? plugin})
      : _plugin = plugin ?? FlutterLocalNotificationsPlugin();

  static const int _dailyId = 1001;

  static const AndroidNotificationDetails _android = AndroidNotificationDetails(
    'practice',
    'Practice reminders',
    channelDescription: 'A gentle daily nudge to keep the learning streak going.',
    importance: Importance.defaultImportance,
    priority: Priority.defaultPriority,
  );

  final FlutterLocalNotificationsPlugin _plugin;

  bool _ready = false;

  /// Set up the plugin and the timezone database. Safe to call more than once.
  ///
  /// Returns false where notifications cannot work at all — the web build, or a
  /// platform that refuses — so a caller can hide the setting rather than offer
  /// a switch that does nothing.
  Future<bool> prepare({String timezoneName = 'Africa/Nairobi'}) async {
    if (_ready) return true;
    if (kIsWeb) return false;

    try {
      tzdata.initializeTimeZones();
      tz.setLocalLocation(tz.getLocation(timezoneName));

      await _plugin.initialize(
        settings: const InitializationSettings(
          android: AndroidInitializationSettings('@mipmap/ic_launcher'),
        ),
      );

      return _ready = true;
    } catch (_) {
      return false;
    }
  }

  /// Ask for permission, on the versions of Android that require it.
  Future<bool> requestPermission() async {
    if (!await prepare()) return false;

    try {
      final android = _plugin
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

      return await android?.requestNotificationsPermission() ?? true;
    } catch (_) {
      return false;
    }
  }

  /// A reminder every day at the chosen time.
  ///
  /// Scheduled inexactly on purpose: an exact alarm needs a permission Android
  /// treats as a privilege, and "some time around five" is exactly as useful
  /// here as "at 17:00:00".
  Future<bool> scheduleDaily({required int hour, required int minute, required String childName}) async {
    if (!await prepare()) return false;

    try {
      await cancel();

      await _plugin.zonedSchedule(
        id: _dailyId,
        title: 'Time for an adventure',
        body: '$childName has missions waiting. A few minutes is plenty!',
        scheduledDate: _nextInstanceOf(hour, minute),
        notificationDetails: const NotificationDetails(android: _android),
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        matchDateTimeComponents: DateTimeComponents.time,
      );

      return true;
    } catch (_) {
      return false;
    }
  }

  Future<void> cancel() async {
    if (!_ready) return;

    try {
      await _plugin.cancel(id: _dailyId);
    } catch (_) {
      // Nothing was scheduled.
    }
  }

  static tz.TZDateTime _nextInstanceOf(int hour, int minute) {
    final now = tz.TZDateTime.now(tz.local);
    var next = tz.TZDateTime(tz.local, now.year, now.month, now.day, hour, minute);

    // A time that has already passed today means tomorrow, not a notification
    // that fires the moment the switch is turned on.
    if (!next.isAfter(now)) {
      next = next.add(const Duration(days: 1));
    }

    return next;
  }
}
