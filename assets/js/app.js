function submitFlag(e, challengeId) {
    e.preventDefault();
    var input = document.getElementById('flagInput');
    var result = document.getElementById('flagResult');
    var btn = e.target.querySelector('.btn-primary');
    var csrfToken = document.getElementById('csrfToken');
    var originalText = btn.textContent;
    var flag = input.value.trim();
    if (!flag) return;

    btn.disabled = true;
    btn.textContent = '验证中...';

    var formData = new FormData();
    formData.append('challenge_id', challengeId);
    formData.append('flag', flag);
    formData.append('csrf_token', csrfToken ? csrfToken.value : '');
    fetch('/challenge.php?cid=' + challengeId, {
        method: 'POST',
        body: formData
    })
    .then(function(r) {
        return r.json().then(function(data) {
            if (!r.ok) throw new Error(data.message || '请求失败');
            return data;
        });
    })
    .then(function(data) {
        result.textContent = data.message;
        result.className = 'flag-result ' + (data.success ? 'success' : 'error');
        if (data.success) {
            input.value = '';
            setTimeout(function() { location.reload(); }, 1500);
        }
    })
    .catch(function(error) {
        result.textContent = error.message || '请求失败，请重试';
        result.className = 'flag-result error';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

function fallbackCopy(text) {
    var area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.style.position = 'fixed';
    area.style.opacity = '0';
    document.body.appendChild(area);
    area.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
    document.body.removeChild(area);
    return ok;
}

// 点击带 data-copy（直接文本）或 data-copy-target（CSS 选择器）的按钮时复制内容
document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-copy], [data-copy-target]');
    if (!btn) return;

    var text = btn.dataset.copy;
    if (!text && btn.dataset.copyTarget) {
        var el = document.querySelector(btn.dataset.copyTarget);
        text = el ? el.textContent : '';
    }
    if (!text) return;

    var finish = function(ok) {
        if (!btn.dataset.label) btn.dataset.label = btn.textContent;
        btn.textContent = ok ? '已复制 ✓' : '复制失败';
        btn.classList.toggle('copied', ok);
        setTimeout(function() {
            btn.textContent = btn.dataset.label;
            btn.classList.remove('copied');
        }, 1500);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(
            function() { finish(true); },
            function() { finish(fallbackCopy(text)); }
        );
    } else {
        finish(fallbackCopy(text));
    }
});

// 题目模拟登录的退出按钮：无刷新清除登录状态条
document.addEventListener('click', function(e) {
    var link = e.target.closest('a[data-logout="1"]');
    if (!link) return;
    e.preventDefault();
    fetch(link.href).then(function() {
        var status = link.closest('.login-status');
        if (status) status.remove();
    });
});

function setupCatalogFilter() {
    var search = document.getElementById('categorySearch');
    var grid = document.getElementById('categoryGrid');
    if (!search || !grid) return;

    var activeDifficulty = 'all';
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.category-card'));
    var empty = document.getElementById('categoryEmpty');
    var buttons = document.querySelectorAll('[data-category-filter]');

    function applyFilter() {
        var term = search.value.trim().toLowerCase();
        var visible = 0;
        cards.forEach(function(card) {
            var matchesText = !term || card.dataset.search.toLowerCase().indexOf(term) !== -1;
            var matchesDifficulty = activeDifficulty === 'all' || card.dataset.difficulties.split(' ').indexOf(activeDifficulty) !== -1;
            card.hidden = !(matchesText && matchesDifficulty);
            if (!card.hidden) visible++;
        });
        if (empty) empty.hidden = visible !== 0;
    }

    search.addEventListener('input', applyFilter);
    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            buttons.forEach(function(item) { item.classList.remove('active'); });
            buttons.forEach(function(item) { item.setAttribute('aria-pressed', 'false'); });
            button.classList.add('active');
            button.setAttribute('aria-pressed', 'true');
            activeDifficulty = button.dataset.categoryFilter;
            applyFilter();
        });
    });
}

function setupChallengeFilter() {
    var search = document.getElementById('challengeSearch');
    var list = document.getElementById('challengeList');
    if (!search || !list) return;

    var activeDifficulty = 'all';
    var items = Array.prototype.slice.call(list.querySelectorAll('.challenge-item'));
    var empty = document.getElementById('challengeEmpty');
    var buttons = document.querySelectorAll('[data-challenge-filter]');

    function applyFilter() {
        var term = search.value.trim().toLowerCase();
        var visible = 0;
        items.forEach(function(item) {
            var matchesText = !term || item.dataset.title.toLowerCase().indexOf(term) !== -1;
            var matchesDifficulty = activeDifficulty === 'all' || item.dataset.difficulty === activeDifficulty;
            item.hidden = !(matchesText && matchesDifficulty);
            if (!item.hidden) visible++;
        });
        if (empty) empty.hidden = visible !== 0;
    }

    search.addEventListener('input', applyFilter);
    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            buttons.forEach(function(item) { item.classList.remove('active'); });
            buttons.forEach(function(item) { item.setAttribute('aria-pressed', 'false'); });
            button.classList.add('active');
            button.setAttribute('aria-pressed', 'true');
            activeDifficulty = button.dataset.challengeFilter;
            applyFilter();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupCatalogFilter();
    setupChallengeFilter();
    document.querySelectorAll('.disclosure-toggle').forEach(function(button) {
        button.addEventListener('click', function() {
            var content = button.nextElementSibling;
            var open = content.classList.toggle('open');
            if (button.classList.contains('hint-toggle')) {
                button.parentElement.classList.toggle('open', open);
            }
            if (button.dataset.altLabel) {
                if (!button.dataset.label) button.dataset.label = button.textContent;
                button.textContent = open ? button.dataset.altLabel : button.dataset.label;
            }
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
});
