<!DOCTYPE html>
<html>
<head>
    <title>Badge Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: sans-serif; padding: 20px; }
        button { margin: 5px; padding: 10px; }
        .badge {
            display: inline-block;
            margin: 3px;
            padding: 5px 10px;
            background: #4caf50;
            color: white;
            border-radius: 5px;
        }
        .trust {
            background: #2196f3;
        }
        .behavior {
            background: #4caf50;
        }
        .warning {
            background: #f44336;
        }
    </style>
</head>
<body>

<h1>Badge Test for {{ $user->username }}</h1>
<p>Trust Score: <strong><span id="trust-score">0</span>%</strong></p>

<div>
    <button onclick="changeTrust(5)">+5 Trust</button>
    <button onclick="changeTrust(10)">+10 Trust</button>
    <button onclick="changeTrust(-5)">-5 Trust</button>
    <button onclick="changeTrust(-10)">-10 Trust</button>
    <button onclick="resetTrust()">Reset</button>
</div>

<h2>Badges</h2>
<div id="badges"></div>

<script>
let user = {
    trustScore: 0,

    // Simulated behavior counters (static for testing meaning)
    publishedPosts: 12,
    publishedComments: 25,
    moderatedCount: 0,
    safeReplies: 12
};

function changeTrust(amount) {
    user.trustScore = Math.max(0, Math.min(100, user.trustScore + amount));
    recalculateBadges();
}

function resetTrust() {
    user.trustScore = 0;
    recalculateBadges();
}

function recalculateBadges() {
    document.getElementById('trust-score').innerText = user.trustScore;

    let badges = [];

    /* ==========================
       TRUST BADGE (ONE ONLY)
    ========================== */
    let trustBadge = null;

    if (user.trustScore >= 100) trustBadge = 'Community Guardian';
    else if (user.trustScore >= 80) trustBadge = 'Trusted Contributor';
    else if (user.trustScore >= 50) trustBadge = 'Community Pillar';
    else if (user.trustScore >= 30) trustBadge = 'Trusted Voice';
    else if (user.trustScore >= 10) trustBadge = 'Regular';
    else trustBadge = 'Newcomer';

    badges.push({ name: trustBadge, type: 'trust' });

    /* ==========================
       BEHAVIOR BADGES (STACK)
    ========================== */
    if (user.publishedPosts >= 10) {
        badges.push({ name: 'Consistent Poster', type: 'behavior' });
    }

    if (user.publishedComments >= 20) {
        badges.push({ name: 'Helpful Commenter', type: 'behavior' });
    }

    if (user.moderatedCount === 0) {
        badges.push({ name: 'Civil Contributor', type: 'behavior' });
    }

    if (user.safeReplies >= 10) {
        badges.push({ name: 'Respectful Debater', type: 'behavior' });
    }

    if (user.publishedPosts > 0 && user.publishedComments >= user.publishedPosts * 3) {
        badges.push({ name: 'Listener', type: 'behavior' });
    }

    if (user.moderatedCount >= 3) {
        badges.push({ name: 'Under Review', type: 'warning' });
    }

    if (user.trustScore < 10) {
        badges.push({ name: 'On Probation', type: 'warning' });
    }

    renderBadges(badges);
}

function renderBadges(badges) {
    const container = document.getElementById('badges');
    container.innerHTML = '';

    badges.forEach(b => {
        const span = document.createElement('span');
        span.className = 'badge ' + b.type;
        span.innerText = b.name;
        container.appendChild(span);
    });
}

// Initial render
recalculateBadges();
</script>

</body>
</html>
