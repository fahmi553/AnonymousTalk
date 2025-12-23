<!DOCTYPE html>
<html>
<head>
    <title>Badge Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: sans-serif; padding: 20px; }
        button { margin: 3px; padding: 8px 12px; }
        .badge { display: inline-block; margin: 3px; padding: 5px 10px; background: #4caf50; color: white; border-radius: 5px; }
        .section { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Badge Test for {{ $user->username }}</h1>
    <p>Trust Score: <span id="trust-score">{{ $user->trust_score }}</span>%</p>

    <div class="section">
        <h3>Trust Controls</h3>
        <button onclick="changeTrust(5)">+5 Trust</button>
        <button onclick="changeTrust(10)">+10 Trust</button>
        <button onclick="changeTrust(-5)">-5 Trust</button>
        <button onclick="changeTrust(-10)">-10 Trust</button>
        <button onclick="resetTrust()">Reset Trust</button>
        <button onclick="recalculate()">Recalculate Badges</button>
    </div>

    <div class="section">
        <h3>Behavior Controls</h3>
        <p>Posts: <span id="posts-count">0</span>
            <button onclick="changePosts(1)">+1</button>
            <button onclick="changePosts(-1)">-1</button>
        </p>
        <p>Comments: <span id="comments-count">0</span>
            <button onclick="changeComments(1)">+1</button>
            <button onclick="changeComments(-1)">-1</button>
        </p>
        <p>Safe Replies: <span id="safe-replies-count">0</span>
            <button onclick="changeSafeReplies(1)">+1</button>
            <button onclick="changeSafeReplies(-1)">-1</button>
        </p>
        <p>Moderated Count: <span id="moderated-count">0</span>
            <button onclick="changeModerated(1)">+1</button>
            <button onclick="changeModerated(-1)">-1</button>
        </p>
    </div>

    <div class="section">
        <h2>Badges:</h2>
        <div id="badges">
            @foreach($user->badges as $badge)
                <span class="badge">{{ $badge->badge_name }}</span>
            @endforeach
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let user = {
            trustScore: {{ $user->trust_score }},
            badges: @json($user->badges->pluck('badge_name')),
            posts: 0,
            comments: 0,
            safeReplies: 0,
            moderated: 0
        };

        function changeTrust(amount) {
            user.trustScore = Math.min(100, Math.max(0, user.trustScore + amount));
            recalculate();
        }

        function changePosts(amount) {
            user.posts = Math.max(0, user.posts + amount);
            $('#posts-count').text(user.posts);
            recalculate();
        }

        function changeComments(amount) {
            user.comments = Math.max(0, user.comments + amount);
            $('#comments-count').text(user.comments);
            recalculate();
        }

        function changeSafeReplies(amount) {
            user.safeReplies = Math.max(0, user.safeReplies + amount);
            $('#safe-replies-count').text(user.safeReplies);
            recalculate();
        }

        function changeModerated(amount) {
            user.moderated = Math.max(0, user.moderated + amount);
            $('#moderated-count').text(user.moderated);
            recalculate();
        }

        function resetTrust() {
            user.trustScore = 0;
            user.posts = 0;
            user.comments = 0;
            user.safeReplies = 0;
            user.moderated = 0;
            $('#posts-count').text(0);
            $('#comments-count').text(0);
            $('#safe-replies-count').text(0);
            $('#moderated-count').text(0);
            recalculate();
        }

        function recalculate() {
            let newBadges = [];

            // Trust-based badges
            // Trust-based badges (highest only)
if (user.trustScore >= 90) newBadges.push('Community Guardian');
else if (user.trustScore >= 80) newBadges.push('Trusted Contributor');
else if (user.trustScore >= 70) newBadges.push('Verified Member');
else if (user.trustScore >= 50) newBadges.push('Community Pillar');
else if (user.trustScore >= 30) newBadges.push('Trusted Voice');
else if (user.trustScore >= 10) newBadges.push('Regular');
else newBadges.push('Newcomer');


            // Behavior badges
            if (user.posts >= 10) newBadges.push('Consistent Poster');
            if (user.comments >= 20) newBadges.push('Helpful Commenter');
            if (user.moderated === 0) newBadges.push('Civil Contributor');
            if (user.safeReplies >= 10) newBadges.push('Respectful Debater');
            if (user.comments >= user.posts * 3 && user.posts > 0) newBadges.push('Listener');
            if (user.moderated >= 3) newBadges.push('Under Review');
            if (user.trustScore < 10) newBadges.push('On Probation');

            user.badges = newBadges;
            updateUI();
        }

        function updateUI() {
            $('#trust-score').text(user.trustScore);
            let badgesHtml = '';
            user.badges.forEach(function(b) {
                badgesHtml += '<span class="badge">' + b + '</span>';
            });
            $('#badges').html(badgesHtml);
        }

        // Initial calculation
        recalculate();
    </script>
</body>
</html>
