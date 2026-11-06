<?php
declare(strict_types=1);
session_start();

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/* ---------- Difficulty levels: key => [label, description] ---------- */
$levels = [
    'easy'   => ['Easy', 'Obvious warning signs. A good place to start.'],
    'medium' => ['Medium', 'Convincing branding and subtler clues.'],
    'hard'   => ['Hard', 'Polished attacks, and real emails that look suspicious.'],
];

/* ---------- Scenarios: 3 per level (all companies and people are fictional) ----------
   Fields: from, addr, to, date, subject, body[], link [label, url]|null,
           attach (file name)|null, answer (real|phishing), why, flags[] */
$bank = [
    'easy' => [
        [
            'from' => 'Prize Center', 'addr' => 'winner@giftcard-promo-win.biz', 'to' => 'you@example.com',
            'date' => 'Mon 2 Nov, 03:27', 'subject' => 'CONGRATULATIONS!!! You won a $500 gift card',
            'body' => ['Dear Lucky User,', 'You have been randomly selected to receive a $500 gift card. This offer expires in 2 hours!', 'Enter your name, address and card number to claim your reward.'],
            'link' => ['Claim my prize', 'http://claim-prize-now.win/gift?id=88213'], 'attach' => null,
            'answer' => 'phishing',
            'why' => 'Unexpected prizes, tiny deadlines and requests for card details are classic scam signals, and the sender domain has no real brand behind it.',
            'flags' => ['You never entered a contest, yet you "won".', 'It asks for card details to claim a free reward.', 'The sender and link use throwaway domains (.biz, .win).'],
        ],
        [
            'from' => 'Skyline Airways', 'addr' => 'bookings@skylineairways.com', 'to' => 'you@example.com',
            'date' => 'Tue 3 Nov, 11:05', 'subject' => 'Booking confirmed: Chicago to Denver, 18 Nov (ref KT7R2P)',
            'body' => ['Hello Jordan Lee,', 'Thanks for booking with Skyline Airways. Your flight departs 18 Nov at 07:45 from Chicago (ORD) and arrives at 09:30 in Denver (DEN).', 'You can check in 24 hours before departure.'],
            'link' => ['Manage booking', 'https://www.skylineairways.com/manage'], 'attach' => null,
            'answer' => 'real',
            'why' => 'It matches a trip you booked, uses your name, and the only link points to the airline\'s own domain.',
            'flags' => ['The sender domain matches the airline.', 'It confirms something you did, with a booking reference.', 'No threats, no deadlines, no request for passwords.'],
        ],
        [
            'from' => 'IT Helpdesk', 'addr' => 'helpdesk@mail-storage-alerts.info', 'to' => 'you@example.com',
            'date' => 'Wed 4 Nov, 08:12', 'subject' => 'Your mailbox is 98% full - action needed',
            'body' => ['Hello user,', 'Your mailbox will stop receiving messages today. Click below and sign in with your email password to increase your storage for free.'],
            'link' => ['Increase storage', 'http://mailbox-upgrade.top/login'], 'attach' => null,
            'answer' => 'phishing',
            'why' => 'Real IT teams do not ask you to type your password into a link. The sender is an unrelated .info domain and the greeting is generic.',
            'flags' => ['Generic greeting ("Hello user").', 'It asks you to sign in through an emailed link.', 'The link goes to an unrelated .top website.'],
        ],
    ],
    'medium' => [
        [
            'from' => 'Meridian IT Notices', 'addr' => 'it-notices@meridiancorp.com', 'to' => 'all-staff@meridiancorp.com',
            'date' => 'Thu 5 Nov, 10:30', 'subject' => 'Planned maintenance: VPN unavailable Saturday 01:00-04:00',
            'body' => ['Hi all,', 'The VPN will be offline for upgrades this Saturday between 01:00 and 04:00. No action is needed on your side and no passwords will be requested.', 'Details are on the intranet under IT > Maintenance. Contact the service desk as usual if you have questions.'],
            'link' => null, 'attach' => null,
            'answer' => 'real',
            'why' => 'It comes from the company\'s own domain, asks for nothing, and points you to the intranet instead of a link.',
            'flags' => ['The sender domain matches the company.', 'No action, login, or link is requested.', 'It sends you to a known internal location.'],
        ],
        [
            'from' => 'TrackRight Delivery', 'addr' => 'delivery@trackright-parcels.com', 'to' => 'you@example.com',
            'date' => 'Fri 30 Oct, 14:03', 'subject' => 'Parcel on hold: pay $1.99 redelivery fee',
            'body' => ['Hello,', 'We tried to deliver your parcel but no one was home. Pay the small redelivery fee within 48 hours or the parcel will be returned.'],
            'link' => ['Pay redelivery fee', 'https://trackright.com-redeliver.shop/pay'], 'attach' => null,
            'answer' => 'phishing',
            'why' => 'The link\'s real host is redeliver.shop. The "trackright.com" part is only a subdomain trick. A tiny fee is a way to capture your card details.',
            'flags' => ['The real domain is the last part before the first "/": com-redeliver.shop.', 'No tracking number or name.', 'A small fee plus a deadline pushes a quick click.'],
        ],
        [
            'from' => 'Brightfold Bank', 'addr' => 'security@brightfoldbank-alerts.com', 'to' => 'you@example.com',
            'date' => 'Sat 31 Oct, 22:41', 'subject' => 'New sign-in from Lagos, Nigeria',
            'body' => ['Hi,', 'We noticed a sign-in to your Brightfold account from a new device in Lagos, Nigeria. If this was not you, secure your account now by confirming your login details.'],
            'link' => ['Secure my account', 'https://brightfoldbank-alerts.com/login'], 'attach' => null,
            'answer' => 'phishing',
            'why' => 'The sender and link use an extra "-alerts" domain, not the bank\'s own site, and the email asks you to log in through the link.',
            'flags' => ['The domain is brightfoldbank-alerts.com, not brightfoldbank.com.', 'Generic greeting with no account details.', 'It wants your login via an emailed link.'],
        ],
    ],
    'hard' => [
        [
            'from' => 'Priya Nair (Orchard Supply)', 'addr' => 'priya.nair@orchardsupp1y.com', 'to' => 'accounts@meridiancorp.com',
            'date' => 'Mon 2 Nov, 15:48', 'subject' => 'RE: Invoice 4471 - updated bank details',
            'body' => ['Hi Sam,', 'Thanks for the call this morning. As discussed, our bank account has changed. Please use the details in the attached remittance form when you pay invoice 4471 this week.', 'Let me know once it is scheduled so I can confirm on our side.'],
            'link' => null, 'attach' => 'Remittance_4471.html',
            'answer' => 'phishing',
            'why' => 'It is polite and plausible, but the domain swaps the letter "l" for the number "1", and bank detail changes should always be confirmed by phone.',
            'flags' => ['"orchardsupp1y" uses a 1 in place of an l.', 'Changing payment details by email is a common invoice fraud.', 'A form sent as an .html attachment can open a fake page.'],
        ],
        [
            'from' => 'CloudNest Billing', 'addr' => 'billing@cloudnest-billing.com', 'to' => 'you@example.com',
            'date' => 'Tue 3 Nov, 13:20', 'subject' => 'Receipt: $249.00 charged to your card (Order 88213)',
            'body' => ['Hello,', 'Thank you for your purchase of CloudNest Pro Annual. Your card has been charged $249.00.', 'If you did not authorize this charge, call our billing team immediately on +1 (888) 555-0142 to cancel and receive a refund.'],
            'link' => null, 'attach' => null,
            'answer' => 'phishing',
            'why' => 'There is no link, but the phone number is the trap. Scammers use a fake charge to get you to call them. Look up a support number on the official site instead.',
            'flags' => ['A surprise charge is meant to alarm you.', 'The only way to respond is a number supplied by the sender.', 'The sender domain is not the service\'s account domain (accounts.cloudnest.com).'],
        ],
        [
            'from' => 'Brightfold Bank', 'addr' => 'alerts@brightfoldbank.com', 'to' => 'you@example.com',
            'date' => 'Wed 4 Nov, 19:56', 'subject' => 'We noticed a new sign-in to your account',
            'body' => ['Hello Jordan,', 'A new sign-in to your Brightfold account was recorded today at 19:52 from a Windows laptop in Chicago. Card ending 4821.', 'If this was you, nothing more to do. If not, open the Brightfold app or call the number on the back of your card.'],
            'link' => null, 'attach' => null,
            'answer' => 'real',
            'why' => 'Security alerts feel urgent, but this one asks for nothing. It uses the bank\'s real domain and sends you to the app or the number already on your card.',
            'flags' => ['The domain is the bank\'s own.', 'No link and no request for login details.', 'It points you to channels you already trust.'],
        ],
    ],
];

/* ---------- Session state ---------- */
function new_game(?string $level = null): array
{
    return ['level' => $level, 'index' => 0, 'score' => 0, 'results' => [], 'feedback' => null];
}

function rating(int $score, int $total): array
{
    $pct = $total > 0 ? $score / $total : 0;
    if ($pct >= 1) {
        return ['Cybersecurity Expert', 'Flawless. You spotted every trick and trusted the genuine email.'];
    }
    if ($pct >= 0.6) {
        return ['Intermediate', 'Solid instincts. Review the one you missed and try a harder level.'];
    }
    return ['Beginner', 'A good start. Attackers count on these mistakes, so check the sender domain and link first.'];
}

if (
    !isset($_SESSION['game']) ||
    !is_array($_SESSION['game']) ||
    !array_key_exists('level', $_SESSION['game'])
) {
    $_SESSION['game'] = new_game();
}

/* ---------- Handle actions (POST, then redirect so refresh can't re-submit) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $g      = $_SESSION['game'];
    $action = $_POST['action'] ?? '';
    $list   = $g['level'] !== null ? $bank[$g['level']] : [];

    if ($action === 'start') {
        $lv = $_POST['level'] ?? '';
        if (isset($levels[$lv])) {
            $g = new_game($lv);
        }
    } elseif ($action === 'answer' && $g['level'] !== null && $g['feedback'] === null && $g['index'] < count($list)) {
        $guess = $_POST['guess'] ?? '';
        if (in_array($guess, ['real', 'phishing'], true)) {
            $correct = $guess === $list[$g['index']]['answer'];
            if ($correct) {
                $g['score']++;
            }
            $g['results'][] = $correct;
            $g['feedback']  = ['guess' => $guess, 'correct' => $correct];
        }
    } elseif ($action === 'next' && $g['feedback'] !== null) {
        $g['index']++;
        $g['feedback'] = null;
    } elseif ($action === 'restart') {
        $g = new_game($g['level']);
    } elseif ($action === 'menu') {
        $g = new_game();
    }

    $_SESSION['game'] = $g;
    header('Location: index.php');
    exit;
}

/* ---------- View state ---------- */
$g      = $_SESSION['game'];
$level  = $g['level'];
$fb     = $g['feedback'];
$list   = $level !== null ? $bank[$level] : [];
$total  = count($list);
$i      = $g['index'];
$done   = $level !== null && $i >= $total;
$s      = ($level !== null && !$done) ? $list[$i] : null;
$completed = $done ? $total : $i + ($fb ? 1 : 0);
[$rTitle, $rMsg] = rating((int) $g['score'], max($total, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inbox Defender | Phishing Training</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="wrap">

    <header class="top">
        <div class="brand">
            <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.5 12 2.5 2.5 4.5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Inbox Defender</span>
        </div>
        <?php if ($level !== null && !$done): ?>
            <form method="post"><input type="hidden" name="action" value="menu"><button class="link-btn">Quit to menu</button></form>
        <?php endif; ?>
    </header>

<?php if ($level === null): /* ===== MENU ===== */ ?>

    <h1>Choose your difficulty</h1>
    <p class="lead">Each level has three emails. Decide whether each one is real or phishing and see how you score.</p>
    <form method="post" class="levels">
        <input type="hidden" name="action" value="start">
        <?php foreach ($levels as $key => [$name, $desc]): ?>
            <button class="level-card lv-<?= $key ?>" name="level" value="<?= $key ?>">
                <strong><?= h($name) ?></strong>
                <span><?= h($desc) ?></span>
            </button>
        <?php endforeach; ?>
    </form>

<?php else: /* ===== GAME + RESULTS share the dashboard header ===== */ ?>

    <section class="tiles">
        <div class="tile"><span class="k">Difficulty</span><span class="v lv-<?= h($level) ?>"><?= h($levels[$level][0]) ?></span></div>
        <div class="tile"><span class="k">Score</span><span class="v"><?= (int) $g['score'] ?> / <?= $total ?></span></div>
        <div class="tile"><span class="k">Completed</span><span class="v"><?= $completed ?> / <?= $total ?></span></div>
    </section>

    <ol class="segments" aria-label="<?= $completed ?> of <?= $total ?> questions completed">
        <?php for ($n = 0; $n < $total; $n++):
            $cls = isset($g['results'][$n]) ? ($g['results'][$n] ? 'ok' : 'bad') : (($n === $i && !$done) ? 'now' : ''); ?>
            <li class="<?= $cls ?>"></li>
        <?php endfor; ?>
    </ol>

    <?php if (!$done): ?>

        <h1><?= $fb ? 'Here is what to look for' : 'Is this email real or phishing?' ?></h1>
        <article class="mail">
            <div class="mail-bar"><i></i><i></i><i></i><span>Email <?= $i + 1 ?> of <?= $total ?></span></div>
            <dl class="mail-meta">
                <dt>From</dt><dd><?= h($s['from']) ?> <span class="addr">&lt;<?= h($s['addr']) ?>&gt;</span></dd>
                <dt>To</dt><dd><?= h($s['to']) ?></dd>
                <dt>Date</dt><dd><?= h($s['date']) ?></dd>
                <dt>Subject</dt><dd class="subject"><?= h($s['subject']) ?></dd>
            </dl>
            <div class="mail-body">
                <?php foreach ($s['body'] as $p): ?><p><?= h($p) ?></p><?php endforeach; ?>
                <?php if ($s['link']): ?>
                    <p><span class="mail-link" tabindex="0" title="<?= h($s['link'][1]) ?>"><?= h($s['link'][0]) ?></span></p>
                <?php endif; ?>
                <?php if ($s['attach']): ?>
                    <p><span class="attach">Attachment: <?= h($s['attach']) ?></span></p>
                <?php endif; ?>
            </div>
        </article>

        <?php if (!$fb): ?>
            <?php if ($s['link']): ?><p class="hint">Hover over the link to preview where it really goes.</p><?php endif; ?>
            <form method="post" class="choices">
                <input type="hidden" name="action" value="answer">
                <button class="btn real" name="guess" value="real">Real</button>
                <button class="btn phish" name="guess" value="phishing">Phishing</button>
            </form>
        <?php else: ?>
            <section class="verdict <?= $fb['correct'] ? 'ok' : 'bad' ?>">
                <h2><?= $fb['correct'] ? 'Correct' : 'Incorrect' ?>: this email is <?= $s['answer'] === 'real' ? 'real' : 'phishing' ?></h2>
                <p>You answered <?= $fb['guess'] === 'real' ? 'Real' : 'Phishing' ?>. <?= h($s['why']) ?></p>
                <?php if ($s['link']): ?><p>Link destination: <code><?= h($s['link'][1]) ?></code></p><?php endif; ?>
                <h3><?= $s['answer'] === 'phishing' ? 'Red flags' : 'Signs it is genuine' ?></h3>
                <ul><?php foreach ($s['flags'] as $f): ?><li><?= h($f) ?></li><?php endforeach; ?></ul>
                <form method="post">
                    <input type="hidden" name="action" value="next">
                    <button class="btn primary"><?= $i + 1 < $total ? 'Next email' : 'See results' ?></button>
                </form>
            </section>
        <?php endif; ?>

    <?php else: /* ===== RESULTS ===== */ ?>

        <h1><?= h($rTitle) ?></h1>
        <p class="lead">You identified <strong><?= (int) $g['score'] ?> of <?= $total ?></strong> emails correctly on <?= h($levels[$level][0]) ?>. <?= h($rMsg) ?></p>

        <ol class="breakdown">
            <?php foreach ($list as $n => $sc): $ok = $g['results'][$n] ?? false; ?>
                <li class="<?= $ok ? 'ok' : 'bad' ?>">
                    <span class="tag"><?= $ok ? 'Correct' : 'Missed' ?></span>
                    <span><?= h($sc['subject']) ?></span>
                    <span class="was"><?= $sc['answer'] === 'real' ? 'Real' : 'Phishing' ?></span>
                </li>
            <?php endforeach; ?>
        </ol>

        <section class="tips">
            <h3>Three habits that stop most phishing</h3>
            <ul>
                <li>Read the sender's full address and compare the domain letter by letter.</li>
                <li>Preview where a link goes, or type the site address yourself.</li>
                <li>Slow down when an email pushes urgency, threats, or requests for passwords and bank details.</li>
            </ul>
        </section>

        <div class="actions">
            <form method="post"><input type="hidden" name="action" value="restart"><button class="btn primary">Play <?= h($levels[$level][0]) ?> again</button></form>
            <form method="post"><input type="hidden" name="action" value="menu"><button class="btn ghost">Change difficulty</button></form>
        </div>

    <?php endif; ?>
<?php endif; ?>

</main>
</body>
</html>
