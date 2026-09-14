/// The learning buddies a child can pick, exactly as the website lists them.
///
/// Mirrors `Child::AVATARS` and the add-child form's roles, so a child created
/// on the website looks the same in the app and the other way round. Kept on
/// the device because the profile picker is the first screen a child sees, and
/// it has to draw with the aeroplane mode on.
class Avatar {
  const Avatar(this.id, this.emoji, this.name, this.role);

  final String id;
  final String emoji;
  final String name;
  final String role;
}

class Avatars {
  const Avatars._();

  static const List<Avatar> all = [
    Avatar('lion', '🦁', 'Leo the Lion', 'Safari Guide'),
    Avatar('elephant', '🐘', 'Eli the Elephant', 'Math Wizard'),
    Avatar('giraffe', '🦒', 'Gigi the Giraffe', 'Word Master'),
    Avatar('monkey', '🐒', 'Milo the Monkey', 'Joyful Play'),
    Avatar('tiger', '🐯', 'Tara the Tiger', 'Brave Thinker'),
    Avatar('fox', '🦊', 'Finn the Fox', 'Swift Solver'),
    Avatar('panda', '🐼', 'Pip the Panda', 'Calm Explorer'),
    Avatar('koala', '🐨', 'Koko the Koala', 'Nature Scout'),
    Avatar('rabbit', '🐰', 'Ruby the Rabbit', 'Speedy Learner'),
    Avatar('frog', '🐸', 'Flick the Frog', 'Curious Leaper'),
    Avatar('owl', '🦉', 'Olive the Owl', 'Wisdom Bird'),
    Avatar('cat', '🐱', 'Cleo the Cat', 'Super Creative'),
    Avatar('dog', '🐶', 'Dash the Dog', 'Loyal Friend'),
    Avatar('cow', '🐮', 'Clover the Cow', 'Farm Champion'),
    Avatar('pig', '🐷', 'Penny the Pig', 'Happy Artist'),
    Avatar('unicorn', '🦄', 'Uma the Unicorn', 'Magic Spark'),
    Avatar('dino', '🦖', 'Rex the Dino', 'Dino Power'),
    Avatar('robot', '🤖', 'Beep the Robot', 'Tech Genius'),
    Avatar('dragon', '🐉', 'Ignis the Dragon', 'Fire Champion'),
  ];

  static final Map<String, Avatar> _byId = {for (final avatar in all) avatar.id: avatar};

  static Avatar? find(String? id) => _byId[id];

  /// The website falls back to 🧒 and "Friend" for an unknown buddy.
  static String emojiFor(String? id) => _byId[id]?.emoji ?? '🧒';

  static String nameFor(String? id) => _byId[id]?.name ?? 'Friend';

  /// Hats from the shop, as `Child::getEquippedHatEmojiAttribute` draws them.
  static const Map<String, String> hats = {
    'hat_star': '🌟',
    'hat_crown': '👑',
    'hat_pirate': '🏴‍☠️',
    'hat_superhero': '🦸',
    'hat_sunglasses': '🕶️',
    'hat_astronaut': '👨‍🚀',
    'hat_dino': '🦖',
    'hat_party': '🥳',
  };

  static String? hatFor(String? id) => id == null ? null : hats[id];
}

/// The CBC grade stages on the add-explorer form, in the website's order.
class GradeStage {
  const GradeStage(this.code, this.label, this.age, this.emoji, this.years);

  /// What is stored as the child's level: "Play Group", "PP1", "Grade 1"…
  final String code;
  final String label;
  final String age;
  final String emoji;

  /// The age this stage stands for, used to estimate a birthday, as the
  /// website does when a grade pill is tapped.
  final int years;

  static const List<GradeStage> all = [
    GradeStage('Play Group', 'Playgroup', 'Ages 2-3', '🧸', 3),
    GradeStage('PP1', 'PP1', 'Age 4', '🎨', 4),
    GradeStage('PP2', 'PP2', 'Age 5', '📖', 5),
    GradeStage('Grade 1', 'Grade 1', 'Age 6', '✏️', 6),
    GradeStage('Grade 2', 'Grade 2', 'Age 7', '📚', 7),
    GradeStage('Grade 3', 'Grade 3', 'Age 8+', '🏆', 8),
  ];

  /// `Child::recommendLevel`: the level a birthday implies.
  static String levelForBirthdate(DateTime birthdate, {DateTime? now}) {
    final today = now ?? DateTime.now();
    var age = today.year - birthdate.year;

    if (today.month < birthdate.month || (today.month == birthdate.month && today.day < birthdate.day)) {
      age--;
    }

    if (age <= 3) return 'Play Group';
    if (age == 4) return 'PP1';
    if (age == 5) return 'PP2';
    if (age == 6) return 'Grade 1';
    if (age == 7) return 'Grade 2';

    return 'Grade 3';
  }
}
