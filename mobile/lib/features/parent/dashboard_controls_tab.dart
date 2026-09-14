part of 'parent_dashboard_screen.dart';

/// Tab 4, "Controls" — subscription, features, screen time and the PIN.
///
/// The website's four controls come first, in its order. Under them sits one
/// card the website has no need for: this device — sync, signing in a TV,
/// downloads and the practice reminder.
class _ControlsTab extends ConsumerStatefulWidget {
  const _ControlsTab({required this.child, required this.guardian, required this.host});

  final Child child;
  final DashboardGuardian guardian;
  final _ParentDashboardScreenState host;

  @override
  ConsumerState<_ControlsTab> createState() => _ControlsTabState();
}

class _ControlsTabState extends ConsumerState<_ControlsTab> {
  late bool _devotional = widget.guardian.enableDevotional;
  late bool _songs = widget.guardian.enableSongsHub;
  late int _minutes = widget.child.dailyTimeLimitMinutes;
  final _pin = TextEditingController();

  bool _savingFeatures = false;
  bool _savingTime = false;
  bool _savingPin = false;

  @override
  void didUpdateWidget(_ControlsTab oldWidget) {
    super.didUpdateWidget(oldWidget);

    if (oldWidget.child.id != widget.child.id) {
      _minutes = widget.child.dailyTimeLimitMinutes;
    }
  }

  @override
  void dispose() {
    _pin.dispose();
    super.dispose();
  }

  Future<void> _saveFeatures() async {
    setState(() => _savingFeatures = true);

    try {
      await ref.read(parentDashboardRepositoryProvider).saveFeatureControls(devotional: _devotional, songs: _songs);
      ref.invalidate(extrasProvider);
      widget.host.flash('✨ Devotional & Feature controls updated successfully!');
      await widget.host.refresh();
    } on ApiException catch (error) {
      widget.host.error(error.message);
    } finally {
      if (mounted) setState(() => _savingFeatures = false);
    }
  }

  Future<void> _saveTime() async {
    setState(() => _savingTime = true);

    try {
      await ref.read(parentDashboardRepositoryProvider).saveTimeLimit(widget.child.id, _minutes);
      widget.host.flash('⏰ Screen time limit updated for ${widget.child.name}!');
      await widget.host.refresh();
    } on ApiException catch (error) {
      widget.host.error(error.message);
    } finally {
      if (mounted) setState(() => _savingTime = false);
    }
  }

  Future<void> _savePin() async {
    final pin = _pin.text.trim();

    if (!RegExp(r'^[0-9]{4}$').hasMatch(pin)) {
      widget.host.error('The new PIN must be exactly 4 digits.');
      return;
    }

    setState(() => _savingPin = true);

    try {
      final changed = await ref.read(parentDashboardRepositoryProvider).updatePin(pin);

      if (!changed) {
        widget.host.error('For your security, unlock the Parent Zone with your PIN again, then change it.');
        if (mounted) context.go('/parent');
        return;
      }

      _pin.clear();
      widget.host.flash('🔐 Parent PIN updated successfully!');
      await widget.host.refresh();
    } on ApiException catch (error) {
      widget.host.error(error.message);
    } finally {
      if (mounted) setState(() => _savingPin = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final child = widget.child;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // M-Pesa subscription.
        _ParentCard(
          borderColor: Tw.emerald500.withValues(alpha: 0.4),
          borderWidth: 2,
          gradient: LinearGradient(colors: [Tw.emerald950.withValues(alpha: 0.6), Tw.slate900]),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const _CardHeading(
                emoji: '🟢',
                title: 'M-Pesa Subscription & Billing',
                subtitle: 'Manage plan & unlock all adventure worlds',
                subtitleColor: Tw.emerald300,
                divider: false,
              ),
              const SizedBox(height: 12),
              _SolidButton(
                label: '💳  Manage M-Pesa Subscription',
                color: Tw.emerald600,
                verticalPadding: 12,
                onPressed: () => context.go('/parent/subscription'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Devotional & songs.
        _ParentCard(
          borderColor: Tw.purple500.withValues(alpha: 0.3),
          borderWidth: 2,
          gradient: LinearGradient(colors: [Tw.slate900, Tw.purple950.withValues(alpha: 0.6)]),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const _CardHeading(
                emoji: '📖',
                title: 'Daily Devotional & Prayer Controls',
                subtitle: 'Toggle morning Bible verse, voice audio, and songs hub for children',
                subtitleColor: Tw.purple300,
                divider: false,
              ),
              const SizedBox(height: 12),
              _ToggleRow(
                title: 'Daily Bible Verse & Morning Prayer',
                subtitle: 'Shows verse, short teaching, and voice audio prayer on app launch',
                value: _devotional,
                onChanged: (value) => setState(() => _devotional = value),
              ),
              const SizedBox(height: 12),
              _ToggleRow(
                title: 'Music & Songs Hub Access',
                subtitle: 'Allows kids to access CBC educational songs & praise melodies',
                value: _songs,
                onChanged: (value) => setState(() => _songs = value),
              ),
              const SizedBox(height: 12),
              _SolidButton(
                label: 'Save Feature Controls',
                color: Tw.purple600,
                busy: _savingFeatures,
                onPressed: _saveFeatures,
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Screen time.
        _ParentCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _CardHeading(
                emoji: child.avatarEmoji,
                title: child.name,
                subtitle: 'Daily Screen Time Limit',
                divider: false,
              ),
              const SizedBox(height: 16),
              LayoutBuilder(
                builder: (context, constraints) {
                  const gap = 8.0;
                  final columns = sm ? 4 : 2;
                  final width = (constraints.maxWidth - gap * (columns - 1)) / columns;

                  return Wrap(
                    spacing: gap,
                    runSpacing: gap,
                    children: [
                      for (final option in const [(0, 'Unlimited'), (30, '30 Mins'), (45, '45 Mins'), (60, '60 Mins')])
                        SizedBox(
                          width: width,
                          child: KidFocusable(
                            onPressed: () => setState(() => _minutes = option.$1),
                            semanticLabel: option.$2,
                            borderRadius: BorderRadius.circular(Tw.roundedXl),
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              alignment: Alignment.center,
                              decoration: BoxDecoration(
                                color: _minutes == option.$1 ? Tw.indigo600 : Colors.transparent,
                                borderRadius: BorderRadius.circular(Tw.roundedXl),
                                border: Border.all(color: _minutes == option.$1 ? Tw.indigo400 : Tw.slate700),
                              ),
                              child: Text(
                                option.$2,
                                style: Tw.text(Tw.xs, color: _minutes == option.$1 ? Tw.white : Tw.slate300),
                              ),
                            ),
                          ),
                        ),
                    ],
                  );
                },
              ),
              const SizedBox(height: 16),
              _SolidButton(label: 'Save Time Limit', color: Tw.indigo600, busy: _savingTime, onPressed: _saveTime),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Starter PIN warning.
        if (!widget.guardian.hasCustomPin) ...[
          Center(
            child: Container(
              constraints: const BoxConstraints(maxWidth: 448),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Tw.amber500.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(Tw.rounded2xl),
                border: Border.all(color: Tw.amber400.withValues(alpha: 0.5)),
              ),
              child: Text(
                "⚠️ You're still using the starter PIN (${widget.guardian.defaultPin}). Set your own 4-digit PIN below.",
                textAlign: TextAlign.center,
                style: Tw.text(Tw.xs, color: Tw.amber200),
              ),
            ),
          ),
          const SizedBox(height: 24),
        ],

        // Change the PIN.
        Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 448),
            child: _ParentCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text('🔐', textAlign: TextAlign.center, style: TextStyle(fontSize: 36)),
                  const SizedBox(height: 4),
                  Text('Change Parent PIN', textAlign: TextAlign.center, style: Tw.text(Tw.base, color: Tw.white, weight: FontWeight.w900)),
                  Text(
                    'Set a new 4-digit PIN for Parent Zone access',
                    textAlign: TextAlign.center,
                    style: Tw.text(Tw.xs, color: Tw.indigo300, weight: FontWeight.w400),
                  ),
                  const SizedBox(height: 16),
                  Text('New 4-Digit PIN', style: Tw.text(Tw.xs, color: Tw.slate300)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _pin,
                    obscureText: true,
                    maxLength: 4,
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    textAlign: TextAlign.center,
                    style: Tw.text(Tw.x2l, color: Tw.white, weight: FontWeight.w900, letterSpacing: 6),
                    decoration: InputDecoration(
                      counterText: '',
                      hintText: 'e.g. 5678',
                      hintStyle: Tw.text(Tw.x2l, color: Tw.slate500, weight: FontWeight.w900, letterSpacing: 2),
                      filled: true,
                      fillColor: Tw.slate800,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(Tw.roundedXl),
                        borderSide: const BorderSide(color: Tw.slate700),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(Tw.roundedXl),
                        borderSide: const BorderSide(color: Tw.indigo500),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  _SolidButton(
                    label: 'Update Parent PIN',
                    color: Tw.emerald600,
                    fontSize: Tw.sm,
                    verticalPadding: 12,
                    busy: _savingPin,
                    onPressed: _savePin,
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(height: 24),

        const _DeviceCard(),
      ],
    );
  }
}

class _ToggleRow extends StatelessWidget {
  const _ToggleRow({required this.title, required this.subtitle, required this.value, required this.onChanged});

  final String title;
  final String subtitle;
  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return KidFocusable(
      onPressed: () => onChanged(!value),
      semanticLabel: title,
      borderRadius: BorderRadius.circular(Tw.roundedXl),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Tw.slate800.withValues(alpha: 0.8),
          borderRadius: BorderRadius.circular(Tw.roundedXl),
          border: Border.all(color: Tw.slate700),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: Tw.text(Tw.xs, color: Tw.white, weight: FontWeight.w900)),
                  Text(subtitle, style: Tw.text(10, color: Tw.slate400, weight: FontWeight.w400)),
                ],
              ),
            ),
            Checkbox(
              value: value,
              onChanged: (checked) => onChanged(checked ?? false),
              activeColor: Tw.indigo600,
              side: const BorderSide(color: Tw.slate700),
              materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
          ],
        ),
      ),
    );
  }
}

/// What only an app needs: sync, a TV to sign in, downloads, a reminder.
class _DeviceCard extends ConsumerStatefulWidget {
  const _DeviceCard();

  @override
  ConsumerState<_DeviceCard> createState() => _DeviceCardState();
}

class _DeviceCardState extends ConsumerState<_DeviceCard> {
  static const _reminderKey = 'reminder';

  bool _reminderOn = false;
  int _hour = 17;
  int _minute = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadReminder());
  }

  Future<void> _loadReminder() async {
    final saved = await ref.read(progressDaoProvider).setting(_reminderKey);
    if (saved == null || !mounted) return;

    final parts = saved.split(':');
    if (parts.length != 2) return;

    setState(() {
      _reminderOn = true;
      _hour = int.tryParse(parts[0]) ?? 17;
      _minute = int.tryParse(parts[1]) ?? 0;
    });
  }

  /// Scheduled on this device, so it arrives whether or not there is data left.
  Future<void> _setReminder(bool on) async {
    final reminders = ref.read(practiceRemindersProvider);
    final dao = ref.read(progressDaoProvider);
    final messenger = ScaffoldMessenger.of(context);

    if (!on) {
      await reminders.cancel();
      await dao.clearSetting(_reminderKey);
      if (mounted) setState(() => _reminderOn = false);
      return;
    }

    if (!await reminders.requestPermission()) {
      messenger.showSnackBar(const SnackBar(content: Text('This device will not allow reminders.')));
      return;
    }

    final name = ref.read(sessionProvider).children.firstOrNull?.name ?? 'Your explorer';
    final scheduled = await reminders.scheduleDaily(hour: _hour, minute: _minute, childName: name);

    if (!scheduled) {
      messenger.showSnackBar(const SnackBar(content: Text('The reminder could not be set on this device.')));
      return;
    }

    await dao.putSetting(_reminderKey, '$_hour:$_minute');
    if (mounted) setState(() => _reminderOn = true);
  }

  Future<void> _pickTime() async {
    final picked = await showTimePicker(context: context, initialTime: TimeOfDay(hour: _hour, minute: _minute));
    if (picked == null) return;

    setState(() {
      _hour = picked.hour;
      _minute = picked.minute;
    });

    if (_reminderOn) await _setReminder(true);
  }

  @override
  Widget build(BuildContext context) {
    final sync = ref.watch(syncEngineProvider);
    final session = ref.watch(sessionProvider);

    return _ParentCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const _CardHeading(
            emoji: '📱',
            title: 'This Device',
            subtitle: 'Sync, TV sign-in, downloads and reminders',
            divider: false,
          ),
          const SizedBox(height: 16),
          _DeviceRow(
            title: sync.state.pending == 0 ? 'Everything is uploaded' : '${sync.state.pending} updates waiting to upload',
            subtitle: sync.state.lastSuccessAt == null ? 'Not synced yet on this device' : 'Last synced ${sync.state.lastSuccessAt}',
            action: 'Sync now',
            onPressed: () => sync.syncNow(childId: session.activeChild?.id, reason: 'parent'),
          ),
          const SizedBox(height: 12),
          _DeviceRow(
            title: '📺 Sign in a TV',
            subtitle: 'Type the code the television shows',
            action: 'Open',
            onPressed: () => context.go('/parent/tv'),
          ),
          const SizedBox(height: 12),
          _DeviceRow(
            title: '📥 Downloads',
            subtitle: 'Worlds kept on this device for offline play',
            action: 'Open',
            onPressed: () => context.go('/downloads'),
          ),
          const SizedBox(height: 12),
          _ToggleRow(
            title: '⏰ Practice reminder at ${TimeOfDay(hour: _hour, minute: _minute).format(context)}',
            subtitle: 'A daily nudge from this device — tap the time to change it',
            value: _reminderOn,
            onChanged: _setReminder,
          ),
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: _pickTime,
              child: Text('Change time', style: Tw.text(Tw.xs, color: Tw.indigo300)),
            ),
          ),
        ],
      ),
    );
  }
}

class _DeviceRow extends StatelessWidget {
  const _DeviceRow({required this.title, required this.subtitle, required this.action, required this.onPressed});

  final String title;
  final String subtitle;
  final String action;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Tw.slate800.withValues(alpha: 0.8),
        borderRadius: BorderRadius.circular(Tw.roundedXl),
        border: Border.all(color: Tw.slate700),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: Tw.text(Tw.xs, color: Tw.white, weight: FontWeight.w900)),
                Text(subtitle, style: Tw.text(10, color: Tw.slate400, weight: FontWeight.w400)),
              ],
            ),
          ),
          const SizedBox(width: 8),
          _SolidButton(label: action, color: Tw.indigo600, expand: false, verticalPadding: 6, onPressed: onPressed),
        ],
      ),
    );
  }
}
