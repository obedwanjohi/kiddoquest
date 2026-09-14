import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/avatars.dart';
import '../../design/components/focus_ring.dart';
import '../../design/site/site_scaffold.dart';
import '../../design/site/tw.dart';

/// "Add Your Little Explorer" — the website's add-child form, drawn the same way.
///
/// Mirrors `resources/views/guardian/children/create.blade.php`: a live passport
/// card at the top, then four numbered steps — name, learning buddy, CBC grade,
/// favourite colour. Picking a grade sets an estimated birthday, and setting an
/// exact birthday picks the grade, exactly as the website's form does.
class AddChildScreen extends ConsumerStatefulWidget {
  const AddChildScreen({super.key});

  @override
  ConsumerState<AddChildScreen> createState() => _AddChildScreenState();
}

class _AddChildScreenState extends ConsumerState<AddChildScreen> {
  /// The website's colour choices, by name, in its order.
  static const Map<String, Color> colors = {
    'purple': Color(0xFFA855F7),
    'blue': Color(0xFF3B82F6),
    'green': Color(0xFF22C55E),
    'pink': Color(0xFFEC4899),
    'orange': Color(0xFFF97316),
    'yellow': Color(0xFFEAB308),
    'red': Color(0xFFEF4444),
    'teal': Color(0xFF14B8A6),
  };

  final _name = TextEditingController();

  String _avatar = 'lion';
  String _color = 'purple';
  String _level = 'PP1';
  late DateTime _birthdate = _estimatedBirthdate(4);
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _name.dispose();
    super.dispose();
  }

  static DateTime _estimatedBirthdate(int years) {
    final now = DateTime.now();
    return DateTime(now.year - years, now.month, now.day);
  }

  void _selectGrade(GradeStage stage) {
    setState(() {
      _level = stage.code;
      _birthdate = _estimatedBirthdate(stage.years);
    });
  }

  Future<void> _pickBirthday() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _birthdate,
      firstDate: DateTime(1901),
      // The website's date field stops a year ago.
      lastDate: DateTime(now.year - 1, now.month, now.day),
      helpText: 'Exact birthday',
    );

    if (picked == null) return;

    setState(() {
      _birthdate = picked;
      _level = GradeStage.levelForBirthdate(picked);
    });
  }

  Future<void> _save() async {
    final name = _name.text.trim();

    if (name.isEmpty) {
      setState(() => _error = 'Please enter your child\'s name or nickname.');
      return;
    }

    setState(() {
      _busy = true;
      _error = null;
    });

    final child = await ref.read(sessionProvider.notifier).addChild(
          name: name,
          avatar: _avatar,
          birthdate: _dateOnly(_birthdate),
          favoriteColor: _color,
          level: _level,
        );

    if (!mounted) return;

    setState(() => _busy = false);

    if (child == null) {
      setState(() => _error = ref.read(sessionProvider).error ?? 'That did not save. Please try again.');
      return;
    }

    ref.read(sessionProvider.notifier).selectChild(child);
    context.go('/map');
  }

  static String _dateOnly(DateTime date) =>
      '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final buddy = Avatars.find(_avatar) ?? Avatars.all.first;

    return SiteScaffold(
      onBack: () => context.go('/profiles'),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Tw.violet50, Tw.purple50, Tw.amber50],
        ),
      ),
      child: SingleChildScrollView(
        padding: EdgeInsets.symmetric(horizontal: sm ? 24 : 16, vertical: sm ? 40 : 24),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 768),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _TopBar(onBack: () => context.go('/profiles')),
                const SizedBox(height: 24),
                _Card(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const _Heading(),
                      const SizedBox(height: 32),
                      _Passport(
                        name: _name.text.trim().isEmpty ? 'Little Explorer' : _name.text.trim(),
                        level: _level,
                        buddy: buddy,
                      ),
                      const SizedBox(height: 32),

                      // 1. Name
                      const _StepLabel(number: 1, text: "What is your child's name or nickname?", required: true),
                      const SizedBox(height: 8),
                      _NameField(controller: _name, onChanged: () => setState(() => _error = null)),
                      if (_error != null) ...[
                        const SizedBox(height: 8),
                        Text('⚠️ $_error', style: Tw.text(Tw.xs, color: Tw.rose600, weight: FontWeight.w900)),
                      ],
                      const SizedBox(height: 28),

                      // 2. Buddy
                      Row(
                        children: [
                          const Expanded(
                            child: _StepLabel(number: 2, text: 'Choose their Learning Buddy', required: true),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Tw.purple50,
                              borderRadius: BorderRadius.circular(999),
                              border: Border.all(color: Tw.purple100),
                            ),
                            child: Text('Tap to pick friend', style: Tw.text(Tw.xs, color: Tw.purple600)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _BuddyGrid(selected: _avatar, onSelect: (id) => setState(() => _avatar = id)),
                      const SizedBox(height: 28),

                      // 3. Grade
                      const _StepLabel(number: 3, text: 'Select CBC Grade or Age Stage'),
                      const SizedBox(height: 8),
                      Text(
                        'This customizes difficulty, font sizes, and voice narration pacing for their age.',
                        style: Tw.text(Tw.xs, color: Tw.slate500),
                      ),
                      const SizedBox(height: 12),
                      _GradeGrid(selected: _level, onSelect: _selectGrade),
                      const SizedBox(height: 16),
                      _BirthdayRow(date: _dateOnly(_birthdate), onPick: _pickBirthday),
                      const SizedBox(height: 28),

                      // 4. Colour
                      Text.rich(
                        TextSpan(
                          text: "4. Child's Favorite Adventure Color ",
                          style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.slate800),
                          children: [
                            TextSpan(
                              text: '(Optional)',
                              style: Tw.text(Tw.xs, color: Tw.slate400, weight: FontWeight.w400),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Their buddy will personalize the dashboard buttons and sparkles with this theme!',
                        style: Tw.text(Tw.xs, color: Tw.slate500),
                      ),
                      const SizedBox(height: 12),
                      _ColorRow(
                        colors: colors,
                        selected: _color,
                        onSelect: (name) => setState(() => _color = name),
                      ),
                      const SizedBox(height: 28),

                      // Submit
                      Container(
                        padding: const EdgeInsets.only(top: 16),
                        decoration: const BoxDecoration(
                          border: Border(top: BorderSide(color: Tw.slate100, width: 2)),
                        ),
                        child: Column(
                          children: [
                            _LaunchButton(busy: _busy, onPressed: _busy ? null : _save),
                            const SizedBox(height: 16),
                            KidFocusable(
                              onPressed: () => context.go('/profiles'),
                              borderRadius: BorderRadius.circular(Tw.roundedXl),
                              semanticLabel: 'Cancel',
                              child: Padding(
                                padding: const EdgeInsets.all(6),
                                child: Text(
                                  '← Cancel',
                                  style: Tw.text(Tw.sm, color: Tw.slate500, weight: FontWeight.w800),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({required this.onBack});

  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        KidFocusable(
          onPressed: onBack,
          borderRadius: BorderRadius.circular(Tw.rounded2xl),
          semanticLabel: 'Back to Profiles',
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: Tw.white.withValues(alpha: 0.8),
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: Border.all(color: Tw.purple200, width: 2),
              boxShadow: Tw.shadowSm,
            ),
            child: Text(
              '←  Back to Profiles',
              style: Tw.text(sm ? Tw.sm : Tw.xs, color: Tw.purple900, weight: FontWeight.w800),
            ),
          ),
        ),
        Flexible(
          child: Container(
            margin: const EdgeInsets.only(left: 8),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Tw.amber100,
              borderRadius: BorderRadius.circular(999),
              border: Border.all(color: Tw.amber300),
            ),
            child: Text(
              '⭐  New Explorer Pass',
              overflow: TextOverflow.ellipsis,
              style: Tw.text(Tw.xs, color: Tw.amber900, weight: FontWeight.w900),
            ),
          ),
        ),
      ],
    );
  }
}

class _Card extends StatelessWidget {
  const _Card({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final radius = BorderRadius.circular(sm ? 36 : Tw.rounded3xl);

    return Container(
      decoration: BoxDecoration(
        color: Tw.white,
        borderRadius: radius,
        border: Border.all(color: Tw.purple100, width: 4),
        boxShadow: Tw.shadow2xl,
      ),
      child: ClipRRect(
        borderRadius: radius,
        child: Stack(
          children: [
            // Soft floating bubbles in two corners.
            Positioned(top: -40, right: -40, child: _Glow(color: Tw.purple200.withValues(alpha: 0.4))),
            Positioned(bottom: -40, left: -40, child: _Glow(color: const Color(0xFFFBCFE8).withValues(alpha: 0.4))),
            Padding(padding: EdgeInsets.all(sm ? 40 : 24), child: child),
          ],
        ),
      ),
    );
  }
}

class _Glow extends StatelessWidget {
  const _Glow({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    // `blur-2xl`: a 40px blur, which turns the disc into a haze.
    return IgnorePointer(
      child: ImageFiltered(
        imageFilter: ui.ImageFilter.blur(sigmaX: 20, sigmaY: 20),
        child: Container(
          width: 128,
          height: 128,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
      ),
    );
  }
}

class _Heading extends StatelessWidget {
  const _Heading();

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final tile = sm ? 96.0 : 80.0;

    return Column(
      children: [
        Container(
          width: tile,
          height: tile,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(Tw.rounded3xl),
            gradient: const LinearGradient(
              begin: Alignment.bottomLeft,
              end: Alignment.topRight,
              colors: [Tw.purple500, Tw.pink500],
            ),
            boxShadow: [
              BoxShadow(color: Tw.purple500.withValues(alpha: 0.2), blurRadius: 25, offset: const Offset(0, 20), spreadRadius: -5),
            ],
          ),
          child: Text('✨', style: TextStyle(fontSize: sm ? 48 : 36)),
        ),
        const SizedBox(height: 16),
        Text(
          'Add Your Little Explorer',
          textAlign: TextAlign.center,
          style: Tw.display(sm ? Tw.x4l : Tw.x2l, color: Tw.slate900, height: 1.15),
        ),
        const SizedBox(height: 6),
        ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 448),
          child: Text(
            'Set up their personalized CBC learning adventure with their very own character buddy!',
            textAlign: TextAlign.center,
            style: Tw.text(sm ? Tw.sm : Tw.xs, color: Tw.slate600),
          ),
        ),
      ],
    );
  }
}

class _Passport extends StatelessWidget {
  const _Passport({required this.name, required this.level, required this.buddy});

  final String name;
  final String level;
  final Avatar buddy;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final box = sm ? 64.0 : 56.0;

    return Container(
      padding: EdgeInsets.all(sm ? 20 : 16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Tw.rounded3xl),
        gradient: const LinearGradient(colors: [Tw.purple600, Tw.indigo600, Tw.purple800]),
        border: Border.all(color: Tw.purple400.withValues(alpha: 0.3), width: 2),
        boxShadow: Tw.shadowXl,
      ),
      child: Row(
        children: [
          Container(
            width: box,
            height: box,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: Tw.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: Border.all(color: Tw.white.withValues(alpha: 0.4)),
            ),
            child: Text(buddy.emoji, style: TextStyle(fontSize: sm ? 36 : 30)),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 4,
                  children: [
                    _Pill(
                      text: 'KIDDOQUEST PASSPORT',
                      background: Tw.white.withValues(alpha: 0.25),
                      color: Tw.white,
                    ),
                    _Pill(text: level.toUpperCase(), background: Tw.amber400, color: Tw.amber950),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Tw.display(sm ? Tw.x2l : Tw.xl, color: Tw.white),
                ),
                Text(
                  'Playing alongside ${buddy.name} ${buddy.emoji}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Tw.text(Tw.xs, color: Tw.purple200, weight: FontWeight.w600),
                ),
              ],
            ),
          ),
          if (sm)
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text('Starting Rewards', style: Tw.text(Tw.xs, color: Tw.purple200)),
                Text('⭐ 0 Stars', style: Tw.text(Tw.lg, color: Tw.amber300, weight: FontWeight.w900)),
              ],
            ),
        ],
      ),
    );
  }
}

class _Pill extends StatelessWidget {
  const _Pill({required this.text, required this.background, required this.color});

  final String text;
  final Color background;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
      decoration: BoxDecoration(color: background, borderRadius: BorderRadius.circular(999)),
      child: Text(text, style: Tw.label(10, color: color)),
    );
  }
}

class _StepLabel extends StatelessWidget {
  const _StepLabel({required this.number, required this.text, this.required = false});

  final int number;
  final String text;
  final bool required;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return Text.rich(
      TextSpan(
        text: '$number. $text',
        style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.slate800),
        children: [
          if (required) const TextSpan(text: ' *', style: TextStyle(color: Tw.rose500)),
        ],
      ),
    );
  }
}

class _NameField extends StatelessWidget {
  const _NameField({required this.controller, required this.onChanged});

  final TextEditingController controller;
  final VoidCallback onChanged;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    const radius = BorderRadius.all(Radius.circular(Tw.rounded2xl));

    return TextField(
      controller: controller,
      maxLength: 255,
      textCapitalization: TextCapitalization.words,
      onChanged: (_) => onChanged(),
      style: Tw.text(sm ? Tw.lg : Tw.base, color: Tw.slate900),
      decoration: InputDecoration(
        counterText: '',
        hintText: 'e.g., Liam, Zuri, Emma, Ethan',
        hintStyle: Tw.text(sm ? Tw.lg : Tw.base, color: Tw.slate400, weight: FontWeight.w600),
        filled: true,
        fillColor: Tw.slate50,
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        suffixIcon: const Padding(
          padding: EdgeInsets.only(right: 12),
          child: Center(widthFactor: 1, child: Text('✍️', style: TextStyle(fontSize: 20))),
        ),
        enabledBorder: const OutlineInputBorder(borderRadius: radius, borderSide: BorderSide(color: Tw.slate200, width: 2)),
        focusedBorder: const OutlineInputBorder(borderRadius: radius, borderSide: BorderSide(color: Tw.purple600, width: 2)),
      ),
    );
  }
}

class _BuddyGrid extends StatelessWidget {
  const _BuddyGrid({required this.selected, required this.onSelect});

  final String selected;
  final ValueChanged<String> onSelect;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    final columns = width >= 768 ? 4 : (width >= 640 ? 3 : 2);

    return Container(
      constraints: const BoxConstraints(maxHeight: 360),
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: Tw.slate50,
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.slate200, width: 2),
      ),
      child: GridView.builder(
        shrinkWrap: true,
        itemCount: Avatars.all.length,
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: columns,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          mainAxisExtent: 116,
        ),
        itemBuilder: (context, index) {
          final avatar = Avatars.all[index];
          final active = avatar.id == selected;

          return KidFocusable(
            onPressed: () => onSelect(avatar.id),
            borderRadius: BorderRadius.circular(Tw.rounded2xl),
            semanticLabel: avatar.name,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: active ? Tw.purple50 : Tw.white,
                    borderRadius: BorderRadius.circular(Tw.rounded2xl),
                    border: Border.all(color: active ? Tw.purple600 : Tw.slate200, width: 2),
                    boxShadow: active
                        ? [
                            const BoxShadow(color: Tw.purple400, spreadRadius: 2),
                            BoxShadow(color: Tw.purple500.withValues(alpha: 0.15), blurRadius: 15, offset: const Offset(0, 10)),
                          ]
                        : null,
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(avatar.emoji, style: const TextStyle(fontSize: 36)),
                      Text(
                        avatar.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        textAlign: TextAlign.center,
                        style: Tw.text(Tw.xs, color: Tw.slate800, weight: FontWeight.w900, height: 1.2),
                      ),
                      Text(
                        avatar.role,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Tw.text(10, color: Tw.purple600),
                      ),
                    ],
                  ),
                ),
                if (active)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      width: 20,
                      height: 20,
                      alignment: Alignment.center,
                      decoration: const BoxDecoration(color: Tw.purple600, shape: BoxShape.circle, boxShadow: Tw.shadowMd),
                      child: Text('✓', style: Tw.text(10, color: Tw.white, weight: FontWeight.w900)),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _GradeGrid extends StatelessWidget {
  const _GradeGrid({required this.selected, required this.onSelect});

  final String selected;
  final ValueChanged<GradeStage> onSelect;

  @override
  Widget build(BuildContext context) {
    final columns = Tw.isSm(context) ? 6 : 3;

    return LayoutBuilder(
      builder: (context, constraints) {
        const gap = 8.0;
        final width = (constraints.maxWidth - gap * (columns - 1)) / columns;

        return Wrap(
          spacing: gap,
          runSpacing: gap,
          children: [
            for (final stage in GradeStage.all)
              SizedBox(
                width: width,
                child: KidFocusable(
                  onPressed: () => onSelect(stage),
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  semanticLabel: '${stage.label}, ${stage.age}',
                  child: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: stage.code == selected ? Tw.purple50 : Tw.white,
                      borderRadius: BorderRadius.circular(Tw.rounded2xl),
                      border: Border.all(color: stage.code == selected ? Tw.purple600 : Tw.slate200, width: 2),
                      boxShadow: stage.code == selected ? const [BoxShadow(color: Tw.purple400, spreadRadius: 2)] : null,
                    ),
                    child: Column(
                      children: [
                        Text(stage.emoji, style: const TextStyle(fontSize: 24)),
                        const SizedBox(height: 4),
                        Text(
                          stage.label,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: Tw.text(Tw.xs, color: Tw.slate900, weight: FontWeight.w900, height: 1.2),
                        ),
                        Text(stage.age, style: Tw.text(10, color: Tw.slate500, weight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}

class _BirthdayRow extends StatelessWidget {
  const _BirthdayRow({required this.date, required this.onPick});

  final String date;
  final VoidCallback onPick;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Tw.slate50,
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.slate200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('🎂 Or set Exact Birthday:', style: Tw.text(Tw.xs, color: Tw.slate600)),
          const SizedBox(height: 12),
          KidFocusable(
            onPressed: onPick,
            borderRadius: BorderRadius.circular(Tw.roundedXl),
            semanticLabel: 'Exact birthday, $date',
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: Tw.white,
                borderRadius: BorderRadius.circular(Tw.roundedXl),
                border: Border.all(color: Tw.slate300),
              ),
              child: Row(
                children: [
                  Expanded(child: Text(date, style: Tw.text(Tw.xs, color: Tw.slate800))),
                  const Icon(Icons.calendar_today_rounded, size: 16, color: Tw.slate600),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ColorRow extends StatelessWidget {
  const _ColorRow({required this.colors, required this.selected, required this.onSelect});

  final Map<String, Color> colors;
  final String selected;
  final ValueChanged<String> onSelect;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: [
        for (final entry in colors.entries)
          KidFocusable(
            onPressed: () => onSelect(entry.key),
            borderRadius: BorderRadius.circular(Tw.rounded2xl),
            semanticLabel: entry.key,
            child: Column(
              children: [
                AnimatedScale(
                  duration: const Duration(milliseconds: 150),
                  scale: entry.key == selected ? 1.1 : 1,
                  child: Container(
                    width: 48,
                    height: 48,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: entry.value,
                      borderRadius: BorderRadius.circular(Tw.rounded2xl),
                      border: Border.all(color: Tw.white, width: 4),
                      boxShadow: [
                        if (entry.key == selected) const BoxShadow(color: Tw.purple600, spreadRadius: 4),
                        ...Tw.shadowMd,
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  entry.key[0].toUpperCase() + entry.key.substring(1),
                  style: Tw.text(10, color: Tw.slate600, weight: FontWeight.w900),
                ),
              ],
            ),
          ),
      ],
    );
  }
}

class _LaunchButton extends StatelessWidget {
  const _LaunchButton({required this.busy, required this.onPressed});

  final bool busy;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return KidFocusable(
      onPressed: onPressed,
      borderRadius: BorderRadius.circular(Tw.rounded2xl),
      semanticLabel: 'Launch Child Adventure',
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(Tw.rounded2xl),
          gradient: const LinearGradient(colors: [Tw.purple600, Tw.pink600, Tw.amber500]),
          boxShadow: [
            BoxShadow(color: Tw.purple500.withValues(alpha: 0.25), blurRadius: 25, spreadRadius: -5, offset: const Offset(0, 20)),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: busy
              ? const [
                  SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 3, color: Tw.white)),
                ]
              : [
                  Text('✨ Launch Child Adventure', style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.white)),
                  const SizedBox(width: 8),
                  Text('→', style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.white)),
                ],
        ),
      ),
    );
  }
}
