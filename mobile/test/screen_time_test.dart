import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/features/screen_time/screen_time.dart';

/// The daily limit is a promise made to a parent, so the arithmetic behind it
/// is worth pinning down. In particular it must never read as more time than
/// the parent allowed, and never as a negative number to a child.
void main() {
  test('no limit set means no limit shown', () {
    const time = ScreenTime(limitMinutes: 0, usedSeconds: 7200);

    expect(time.unlimited, isTrue);
    expect(time.isOver, isFalse);
  });

  test('minutes left counts down from the limit', () {
    const time = ScreenTime(limitMinutes: 30, usedSeconds: 600);

    expect(time.usedMinutes, 10);
    expect(time.minutesLeft, 20);
    expect(time.isOver, isFalse);
  });

  test('reaching the limit ends the day', () {
    const time = ScreenTime(limitMinutes: 30, usedSeconds: 1800);

    expect(time.minutesLeft, 0);
    expect(time.isOver, isTrue);
  });

  test('overrunning never shows negative time', () {
    const time = ScreenTime(limitMinutes: 30, usedSeconds: 5400);

    expect(time.minutesLeft, 0);
    expect(time.isOver, isTrue);
  });

  test('the last five minutes are flagged so the app can warn', () {
    expect(const ScreenTime(limitMinutes: 30, usedSeconds: 1500).isNearlyOver, isTrue);
    expect(const ScreenTime(limitMinutes: 30, usedSeconds: 600).isNearlyOver, isFalse);
  });

  test('a finished day is not also "nearly over"', () {
    expect(const ScreenTime(limitMinutes: 30, usedSeconds: 1800).isNearlyOver, isFalse);
  });

  test('seconds round to the nearest minute rather than truncating', () {
    // 90 seconds is a minute and a half of a young child's attention; calling
    // that one minute understates the limit every single session.
    expect(const ScreenTime(limitMinutes: 30, usedSeconds: 90).usedMinutes, 2);
  });
}
