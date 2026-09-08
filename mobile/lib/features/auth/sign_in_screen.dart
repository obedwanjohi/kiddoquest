import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// Where a parent signs in. Children never see this screen.
class SignInScreen extends ConsumerStatefulWidget {
  const SignInScreen({super.key, this.startOnRegister = false});

  final bool startOnRegister;

  @override
  ConsumerState<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends ConsumerState<SignInScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _phone = TextEditingController();

  late bool _registering = widget.startOnRegister;
  bool _busy = false;
  bool _obscure = true;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    _phone.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _busy = true);

    final session = ref.read(sessionProvider.notifier);
    final ok = _registering
        ? await session.register(
            name: _name.text,
            email: _email.text,
            password: _password.text,
            phone: _phone.text.isEmpty ? null : _phone.text,
          )
        : await session.signIn(email: _email.text, password: _password.text);

    if (!mounted) return;
    setState(() => _busy = false);

    if (ok) {
      final children = ref.read(sessionProvider).children;
      context.go(children.isEmpty ? '/add-child' : '/profiles');
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final error = ref.watch(sessionProvider).error;

    return KidScaffold(
      maxContentWidth: 520,
      child: SingleChildScrollView(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SizedBox(height: KidSpacing.xl * formFactor.density),
            const Center(child: MascotStage(mood: MascotMood.talking)),
            SizedBox(height: KidSpacing.md * formFactor.density),
            Text(
              _registering ? 'Create your family account' : 'Welcome back',
              textAlign: TextAlign.center,
              style: theme.textTheme.displayMedium,
            ),
            SizedBox(height: KidSpacing.xs * formFactor.density),
            Text(
              _registering
                  ? 'One account for the whole family. You add each child next.'
                  : 'Sign in as a parent, then pick who is playing.',
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
            SizedBox(height: KidSpacing.lg * formFactor.density),
            Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (_registering) ...[
                    TextFormField(
                      controller: _name,
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(labelText: 'Your name'),
                      validator: (value) =>
                          (value == null || value.trim().isEmpty) ? 'Please tell us your name' : null,
                    ),
                    SizedBox(height: KidSpacing.md * formFactor.density),
                  ],
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    autofillHints: const [AutofillHints.email],
                    decoration: const InputDecoration(labelText: 'Email'),
                    validator: (value) {
                      final text = value?.trim() ?? '';
                      if (text.isEmpty) return 'Enter your email';
                      if (!text.contains('@') || !text.contains('.')) return 'That does not look like an email';
                      return null;
                    },
                  ),
                  SizedBox(height: KidSpacing.md * formFactor.density),
                  TextFormField(
                    controller: _password,
                    obscureText: _obscure,
                    textInputAction: TextInputAction.done,
                    onFieldSubmitted: (_) => _submit(),
                    decoration: InputDecoration(
                      labelText: 'Password',
                      suffixIcon: IconButton(
                        onPressed: () => setState(() => _obscure = !_obscure),
                        icon: Icon(_obscure ? Icons.visibility : Icons.visibility_off),
                        tooltip: _obscure ? 'Show password' : 'Hide password',
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) return 'Enter your password';
                      if (_registering && value.length < 8) return 'Use at least 8 characters';
                      return null;
                    },
                  ),
                  if (_registering) ...[
                    SizedBox(height: KidSpacing.md * formFactor.density),
                    TextFormField(
                      controller: _phone,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(labelText: 'M-Pesa phone (optional)'),
                    ),
                  ],
                ],
              ),
            ),
            if (error != null) ...[
              SizedBox(height: KidSpacing.md * formFactor.density),
              Container(
                padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
                decoration: const BoxDecoration(
                  color: Color(0xFFFEE2E2),
                  borderRadius: KidRadius.button,
                ),
                child: Text(
                  error,
                  style: theme.textTheme.bodyLarge?.copyWith(color: const Color(0xFF991B1B)),
                ),
              ),
            ],
            SizedBox(height: KidSpacing.lg * formFactor.density),
            KidButton(
              label: _registering ? 'Create account' : 'Sign in',
              onPressed: _busy ? null : _submit,
              busy: _busy,
              expand: true,
              autofocus: true,
            ),
            SizedBox(height: KidSpacing.md * formFactor.density),
            TextButton(
              onPressed: _busy
                  ? null
                  : () => setState(() {
                        _registering = !_registering;
                      }),
              child: Text(
                _registering ? 'I already have an account' : 'Create a new account',
                style: theme.textTheme.labelLarge?.copyWith(color: KidColors.primary),
              ),
            ),
            SizedBox(height: KidSpacing.xl * formFactor.density),
          ],
        ),
      ),
    );
  }
}
