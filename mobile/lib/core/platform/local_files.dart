/// Local file storage, where there is any.
///
/// A phone caches a pack's pictures and audio on disk so a child can play with
/// no network. A browser has no such disk, and importing `dart:io` at all stops
/// a web build from compiling. So the two are separated here: everything that
/// touches a file goes through this one door, and the web build gets a version
/// that politely does nothing.
///
/// The condition picks the `dart:io` implementation wherever `dart:io` exists,
/// which is right for JavaScript and WebAssembly alike.
library;

export 'local_files_web.dart' if (dart.library.io) 'local_files_io.dart';
