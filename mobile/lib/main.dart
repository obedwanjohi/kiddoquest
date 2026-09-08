import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app/providers.dart';
import 'app/router.dart';
import 'core/platform/device_identity.dart';
import 'core/platform/form_factor.dart';
import 'design/theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Who this device is, and whether it is a television. Both are needed before
  // the first frame: the form factor decides the entire layout.
  await DeviceIdentity.instance.load();

  if (TelevisionDetector.isTelevision) {
    await SystemChrome.setPreferredOrientations([
      DeviceOrientation.landscapeLeft,
      DeviceOrientation.landscapeRight,
    ]);
  }

  runApp(const ProviderScope(child: KiddoQuestApp()));
}

class KiddoQuestApp extends ConsumerStatefulWidget {
  const KiddoQuestApp({super.key});

  @override
  ConsumerState<KiddoQuestApp> createState() => _KiddoQuestAppState();
}

class _KiddoQuestAppState extends ConsumerState<KiddoQuestApp> {
  @override
  void initState() {
    super.initState();

    // Start pushing the outbox as soon as the app is alive. It costs nothing
    // when there is nothing to send.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(syncEngineProvider).start();
    });
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'KiddoQuest',
      debugShowCheckedModeBanner: false,
      routerConfig: router,
      builder: (context, child) {
        final formFactor = resolveFormFactor(context);

        return FormFactorScope(
          formFactor: formFactor,
          child: Theme(
            data: MediaQuery.platformBrightnessOf(context) == Brightness.dark
                ? KidTheme.dark(formFactor)
                : KidTheme.light(formFactor),
            child: child ?? const SizedBox.shrink(),
          ),
        );
      },
      theme: KidTheme.light(FormFactor.compact),
      darkTheme: KidTheme.dark(FormFactor.compact),
    );
  }
}
