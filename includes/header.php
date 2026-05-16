<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function render_header(string $title, string $active = 'dashboard'): void
{
    $user = current_user();
    $role = $user['role'] ?? 'guest';
    $searchValue = trim((string) ($_GET['q'] ?? ''));
    $prefix = match ($role) {
        'admin' => 'admin',
        'faculty' => 'faculty',
        default => 'student',
    };
    $nav = $role === 'faculty'
        ? [
            'dashboard' => ['Dashboard', 'dashboard', "$prefix/dashboard.php"],
            'announcements' => ['Announcements', 'campaign', "$prefix/announcements.php"],
        ]
        : [
            'dashboard' => ['Dashboard', 'dashboard', "$prefix/dashboard.php"],
            'complaints' => ['Complaints', 'assignment_late', "$prefix/complaints.php"],
            'events' => ['Events', 'event_note', "$prefix/events.php"],
            'fyp' => ['FYP Hub', 'science', "$prefix/fyp.php"],
            'announcements' => ['Announcements', 'campaign', "$prefix/announcements.php"],
        ];
    $avatar = $role === 'admin'
        ? 'https://lh3.googleusercontent.com/aida-public/AB6AXuAmwl4R4hKWuIvFYQOdcOLX3VnZOnw1UWQYQBMpYHtJYCt4NF_V5_088wynTgWldY1YiuH8Ei3Evfp42aDgdAKaQ6Yo6q-4bhJpDyM93j53_rBu7_Z0KsipCdmxb2z-mRFWHDRrw36BZFLSyULynSRA6U9BoSk8n5BUDlq9z4RI0dVBftd5Yi_Jrcs7i1kmJd-dwkws5wLdupE2v3aY6itchLt36tN2Kj8TamSgHUjekI3fuQE2X-v2Yy7K1RpHj4IUt2Syvi7xiQ0n'
        : ($role === 'faculty'
            ? 'https://lh3.googleusercontent.com/aida-public/AB6AXuC2A6LrOo7spjvGdu14s0LrQ_7oU5wqL5CPuM2S_F8F2QdqR72vE8LIpEAn7S5Izkxpxvdb8rN5Sx7hJemScLAdtQGgV6sYI6vtW1qMiW4zEszQ94w4UlH0CnELq3v8zw9kkN6AYV1B4pvaQq5VJ4fa8jz1v2fchmHLnlG4kq5Fgmy8g2iJ9Bjqk0z2JgQjR7YQd2vx9h1SHlu2lcC5u3K4H'
            : 'https://lh3.googleusercontent.com/aida-public/AB6AXuDCPbna-rkeWw4BNj5DPQrB3jSYSMBueG4vqj3hEmDTOQRbk609ZhA7ZvYrzwPLm_TxmWU4upAOW60apTZYDSmffFnkoWAWsbGOR9n2-4SheEBvBThagX7jmTEWjIGfxJAfRNow9gBUsoY-gwBV6RNNOUaY0_vFoD2qXairsZAbh8Ru3JyIYS0-Q46s4VYObF20DUnoK_qvEvEvSCzPqMLknmMGXJYxgQfhSH4Q0U1dPTuJHwtuHtxxp1Udbe45yuwQyeJlWJT5Pr7h');
    ?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta name="csrf-token" content="<?= h(csrf_token()) ?>">
<title><?= h($title) ?> | CUSIT Smart Campus Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "secondary-fixed": "#eef4ff", "on-surface-variant": "#5a6f8d", "inverse-on-surface": "#f8fbff",
        "inverse-surface": "#10233f", "surface-container": "#eef4ff", "primary-container": "#ffffff",
        "primary-fixed-dim": "#9fbff0", "inverse-primary": "#d7e6ff", "on-primary": "#10325c",
        "on-surface": "#10233f", "primary": "#0b5ed7", "tertiary": "#6f64d8",
        "surface-container-lowest": "#f8fbff", "error-container": "#ffe4e1", "surface-bright": "#ffffff",
        "outline": "#b9c7da", "surface-container-highest": "#e4edf9", "on-background": "#10233f",
        "primary-fixed": "#dcebff", "surface-tint": "#0b5ed7", "secondary": "#0b5ed7",
        "on-primary-fixed": "#0f2a4d", "surface": "#ffffff", "on-tertiary-fixed-variant": "#4b3fb4",
        "tertiary-fixed": "#ebe7ff", "on-tertiary-fixed": "#2d237c", "on-error-container": "#8f1d1d",
        "on-secondary": "#ffffff", "surface-container-high": "#edf3fc", "secondary-container": "#0b5ed7",
        "tertiary-container": "#f1eeff", "surface-variant": "#eef4ff", "background": "#f4f8fc",
        "error": "#c23b3b", "secondary-fixed-dim": "#cfe0ff", "surface-container-low": "#f9fbff",
        "outline-variant": "#d6e0ee", "on-error": "#ffffff", "on-secondary-container": "#ffffff"
      },
      borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
      spacing: {"xl": "32px", "md": "16px", "sm": "8px", "lg": "24px", "base": "4px", "xs": "4px", "margin-mobile": "16px", "gutter": "20px", "2xl": "48px", "margin-desktop": "40px"},
      fontFamily: {"label-md": ["Inter"], "body-md": ["Inter"], "body-sm": ["Inter"], "headline-md": ["Outfit"], "headline-lg": ["Outfit"], "display-lg": ["Outfit"]},
      fontSize: {"label-md": ["0.875rem", "1.25rem"], "body-md": ["1rem", "1.5rem"], "body-sm": ["0.875rem", "1.25rem"], "headline-md": ["1.375rem", "1.75rem"], "headline-lg": ["2rem", "2.5rem"], "display-lg": ["3.5rem", "4rem"]}
    }
  }
}
</script>
<style>
body{--portal-bg:#f4f8fc;--portal-text:#10233f;--portal-muted:#5a6f8d;--portal-muted-soft:rgba(90,111,141,.72);--portal-panel:rgba(255,255,255,.94);--portal-panel-strong:rgba(255,255,255,.98);--portal-border:rgba(11,94,215,.12);--portal-border-soft:rgba(11,94,215,.08);--portal-sidebar-bg:linear-gradient(180deg,#0b5ed7 0%,#0a4fb5 100%);--portal-sidebar-text:#ffffff;--portal-sidebar-muted:rgba(255,255,255,.74);--portal-header-bg:rgba(255,255,255,.92);--portal-input-bg:#ffffff;--portal-input-border:#d6e0ee;--portal-accent:#0b5ed7;--portal-accent-soft:#5f9cff;--portal-accent-strong:#0f2a4d;--portal-tertiary-text:#6f64d8;--portal-accent-ring:rgba(11,94,215,.22);--portal-accent-glow:rgba(11,94,215,.08);--portal-gradient-start:#0b5ed7;--portal-gradient-end:#6f64d8;--portal-chip-bg:rgba(11,94,215,.08);--portal-chip-border:rgba(11,94,215,.14);--portal-chat-user-start:rgba(11,94,215,.95);--portal-chat-user-end:rgba(85,113,191,.95);background:var(--portal-bg);color:var(--portal-text)}
body.theme-emerald{--portal-bg:#0d1512;--portal-text:#e6fff3;--portal-muted:#98cdb8;--portal-muted-soft:rgba(152,205,184,.72);--portal-panel:rgba(17,36,30,.66);--portal-panel-strong:rgba(12,23,20,.95);--portal-border:rgba(110,231,183,.12);--portal-border-soft:rgba(110,231,183,.1);--portal-sidebar-bg:linear-gradient(180deg,#0f9f6e 0%,#0d815a 100%);--portal-sidebar-text:#effff7;--portal-sidebar-muted:rgba(239,255,247,.74);--portal-header-bg:rgba(12,23,20,.7);--portal-input-bg:#12211d;--portal-input-border:#29574b;--portal-accent:#0f9f6e;--portal-accent-soft:#79f2c0;--portal-accent-strong:#d7ffef;--portal-tertiary-text:#8bc4ff;--portal-accent-ring:rgba(15,159,110,.3);--portal-accent-glow:rgba(15,159,110,.1);--portal-gradient-start:#79f2c0;--portal-gradient-end:#8bc4ff;--portal-chip-bg:rgba(121,242,192,.1);--portal-chip-border:rgba(121,242,192,.18);--portal-chat-user-start:rgba(15,159,110,.92);--portal-chat-user-end:rgba(43,94,78,.92)}
body.theme-sunset{--portal-bg:#17100f;--portal-text:#fff3eb;--portal-muted:#d7b3a1;--portal-muted-soft:rgba(215,179,161,.72);--portal-panel:rgba(45,27,24,.66);--portal-panel-strong:rgba(27,18,17,.95);--portal-border:rgba(251,146,60,.12);--portal-border-soft:rgba(251,146,60,.09);--portal-sidebar-bg:linear-gradient(180deg,#f97316 0%,#d55d0e 100%);--portal-sidebar-text:#fff7f1;--portal-sidebar-muted:rgba(255,247,241,.74);--portal-header-bg:rgba(27,18,17,.7);--portal-input-bg:#241715;--portal-input-border:#6b3e2c;--portal-accent:#f97316;--portal-accent-soft:#fdba74;--portal-accent-strong:#ffedd5;--portal-tertiary-text:#f0abfc;--portal-accent-ring:rgba(249,115,22,.28);--portal-accent-glow:rgba(249,115,22,.1);--portal-gradient-start:#fdba74;--portal-gradient-end:#f0abfc;--portal-chip-bg:rgba(253,186,116,.1);--portal-chip-border:rgba(253,186,116,.18);--portal-chat-user-start:rgba(249,115,22,.92);--portal-chat-user-end:rgba(145,72,38,.92)}
.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
.glass-panel,.glass-card{background:var(--portal-panel);backdrop-filter:blur(16px);border:1px solid var(--portal-border)}
.neon-glow-primary{box-shadow:0 0 20px var(--portal-accent-ring)}.gradient-text{background:linear-gradient(135deg,var(--portal-gradient-start),var(--portal-gradient-end));-webkit-background-clip:text;color:transparent}
.scrollbar-hide::-webkit-scrollbar{display:none}.form-input{background:var(--portal-input-bg);border:1px solid var(--portal-input-border);border-radius:.75rem;color:var(--portal-text);width:100%}.form-input:focus{outline:none;border-color:var(--portal-accent-soft);box-shadow:0 0 0 1px var(--portal-accent-soft)}
.portal-sidebar{background:var(--portal-sidebar-bg)!important}.portal-header{background:var(--portal-header-bg)!important;border-bottom-color:var(--portal-border)!important}
.portal-sidebar .text-primary,.portal-sidebar .text-on-surface,.portal-sidebar .text-on-surface-variant,.portal-sidebar .text-secondary-fixed{color:var(--portal-sidebar-text)!important}
.portal-sidebar .text-on-surface-variant\/70{color:var(--portal-sidebar-muted)!important}
.portal-sidebar .hover\:text-on-surface:hover{color:var(--portal-sidebar-text)!important}
.portal-sidebar .hover\:bg-white\/10:hover{background:rgba(255,255,255,.12)!important}
.portal-sidebar .border-white\/5{border-color:rgba(255,255,255,.12)!important}
.portal-header .text-on-surface,.portal-main .text-on-surface{color:var(--portal-text)!important}
.portal-header .text-on-surface-variant,.portal-main .text-on-surface-variant{color:var(--portal-muted)!important}
.portal-header .text-secondary,.portal-main .text-secondary{color:var(--portal-accent)!important}
.portal-header .text-tertiary,.portal-main .text-tertiary{color:var(--portal-tertiary-text)!important}
.portal-header .text-primary,.portal-main .text-primary{color:var(--portal-accent-strong)!important}
.portal-header .bg-primary-container{background:#ffffff!important}
.portal-header .border-white\/10{border-color:#d6e0ee!important}
.portal-header input{color:var(--portal-text)!important}
.portal-header input::placeholder{color:var(--portal-muted-soft)!important}
.portal-main .border-white\/5,.portal-main .border-white\/10{border-color:#dbe6f3!important}
.portal-main .bg-white\/5{background:rgba(11,94,215,.03)!important}
.portal-main .bg-surface-container,.portal-main .bg-surface-container-low,.portal-main .bg-surface-container-high,.portal-main .bg-surface-container-highest{background:var(--portal-input-bg)!important}
.portal-main .text-error,.portal-header .text-error{color:var(--error)!important}
.floating-ai-chat{position:fixed;right:18px;bottom:24px;z-index:60;display:flex;align-items:center;justify-content:center;width:58px;height:58px;border-radius:9999px;background:linear-gradient(135deg,var(--portal-accent),var(--portal-accent-soft));color:#e6ecff;border:1px solid rgba(255,255,255,.14);box-shadow:0 18px 38px var(--portal-accent-ring),0 0 0 6px var(--portal-accent-glow);transition:transform .2s ease,box-shadow .2s ease}
.floating-ai-chat:hover{transform:translateY(-2px);box-shadow:0 22px 44px var(--portal-accent-ring),0 0 0 8px var(--portal-accent-glow)}
.floating-ai-chat .material-symbols-outlined{font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;font-size:28px}
.floating-ai-chat-indicator{position:absolute;top:7px;right:7px;width:10px;height:10px;border-radius:9999px;background:var(--portal-gradient-end);border:2px solid #131315}
.ai-chat-panel{position:fixed;right:18px;bottom:96px;z-index:65;width:min(420px,calc(100vw - 24px));height:min(680px,calc(100vh - 120px));display:flex;flex-direction:column;overflow:hidden;border-radius:20px;background:var(--portal-panel-strong);backdrop-filter:blur(18px);border:1px solid var(--portal-border);box-shadow:0 28px 60px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.02);transform:translateY(18px) scale(.98);opacity:0;pointer-events:none;transition:opacity .22s ease,transform .22s ease}
.ai-chat-panel.is-open{opacity:1;pointer-events:auto;transform:translateY(0) scale(1)}
.ai-chat-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--portal-border-soft);background:linear-gradient(180deg,var(--portal-accent-ring),rgba(5,102,217,0))}
.ai-chat-brand{display:flex;align-items:center;gap:12px;min-width:0}
.ai-chat-brand-badge{display:flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,var(--portal-accent),var(--portal-accent-soft));color:#e6ecff;box-shadow:0 12px 24px var(--portal-accent-ring)}
.ai-chat-title{font-family:Outfit,sans-serif;font-size:1.1rem;line-height:1.4rem;font-weight:700;color:var(--portal-text)}
.ai-chat-subtitle{font-family:Inter,sans-serif;font-size:.82rem;line-height:1.15rem;color:var(--portal-muted-soft)}
.ai-chat-close{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:9999px;border:1px solid var(--portal-border-soft);background:rgba(255,255,255,.04);color:#c6c6cd;transition:background .2s ease,color .2s ease}
.ai-chat-close:hover{background:rgba(255,255,255,.08);color:#e4e2e4}
.ai-chat-body{flex:1;overflow-y:auto;padding:18px;background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,0))}
.ai-chat-stack{display:flex;flex-direction:column;gap:14px}
.ai-chat-message{display:flex;gap:10px;align-items:flex-start}
.ai-chat-message.is-user{justify-content:flex-end}
.ai-chat-avatar{flex:0 0 auto;display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:12px;background:rgba(255,255,255,.06);color:var(--portal-accent-soft);border:1px solid rgba(255,255,255,.06)}
.ai-chat-bubble{max-width:82%;padding:12px 14px;border-radius:18px;background:var(--portal-input-bg);border:1px solid rgba(255,255,255,.06);color:var(--portal-text);font-family:Inter,sans-serif;font-size:.95rem;line-height:1.45rem;white-space:pre-wrap;word-break:break-word}
.ai-chat-message.is-user .ai-chat-bubble{background:linear-gradient(135deg,var(--portal-chat-user-start),var(--portal-chat-user-end));border-color:rgba(173,198,255,.18);color:#f5f7ff;border-bottom-right-radius:8px}
.ai-chat-message.is-assistant .ai-chat-bubble{border-bottom-left-radius:8px}
.ai-chat-message.is-error .ai-chat-bubble{background:rgba(147,0,10,.18);border-color:rgba(255,180,171,.18);color:#ffb4ab}
.ai-chat-suggestions{display:flex;flex-wrap:wrap;gap:8px;padding:0 18px 16px}
.ai-chat-chip{padding:8px 12px;border-radius:9999px;border:1px solid var(--portal-chip-border);background:var(--portal-chip-bg);color:var(--portal-accent-strong);font-family:Inter,sans-serif;font-size:.82rem;line-height:1rem;transition:background .2s ease,border-color .2s ease}
.ai-chat-chip:hover{background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.24)}
.ai-chat-composer{padding:14px 16px 16px;border-top:1px solid var(--portal-border-soft);background:var(--portal-panel-strong)}
.ai-chat-form{display:flex;align-items:flex-end;gap:10px;padding:10px;border-radius:18px;background:var(--portal-input-bg);border:1px solid var(--portal-border-soft)}
.ai-chat-input{flex:1;min-height:24px;max-height:140px;padding:4px 2px;background:transparent;border:none;resize:none;color:var(--portal-text);font-family:Inter,sans-serif;font-size:.95rem;line-height:1.45rem}
.ai-chat-input:focus{outline:none}
.ai-chat-input::placeholder{color:var(--portal-muted-soft)}
.ai-chat-send{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:42px;height:42px;border-radius:14px;border:1px solid rgba(173,198,255,.16);background:linear-gradient(135deg,var(--portal-accent),var(--portal-accent-soft));color:#e6ecff;box-shadow:0 10px 18px var(--portal-accent-ring);transition:transform .2s ease,opacity .2s ease}
.ai-chat-send:hover{transform:translateY(-1px)}
.ai-chat-send:disabled{opacity:.45;transform:none;cursor:not-allowed}
.ai-chat-footer-note{margin-top:8px;padding:0 4px;font-family:Inter,sans-serif;font-size:.76rem;line-height:1rem;color:var(--portal-muted-soft)}
.theme-switcher{position:relative}
.theme-toggle{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:9999px;border:1px solid var(--portal-border-soft);background:rgba(255,255,255,.04);color:var(--portal-muted);transition:background .2s ease,color .2s ease,border-color .2s ease}
.theme-toggle:hover{background:rgba(255,255,255,.08);color:var(--portal-text);border-color:var(--portal-border)}
.theme-menu{position:absolute;top:52px;right:0;min-width:220px;padding:12px;border-radius:18px;background:var(--portal-panel-strong);border:1px solid var(--portal-border);box-shadow:0 20px 48px rgba(0,0,0,.38);opacity:0;pointer-events:none;transform:translateY(10px);transition:opacity .2s ease,transform .2s ease}
.theme-menu.is-open{opacity:1;pointer-events:auto;transform:translateY(0)}
.theme-menu-title{font-family:Outfit,sans-serif;font-size:1rem;line-height:1.35rem;font-weight:700;color:var(--portal-text)}
.theme-menu-subtitle{font-family:Inter,sans-serif;font-size:.78rem;line-height:1.05rem;color:var(--portal-muted-soft)}
.theme-options{display:grid;gap:10px;margin-top:12px}
.theme-option{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;padding:11px 12px;border-radius:14px;border:1px solid var(--portal-border-soft);background:rgba(255,255,255,.03);text-align:left;transition:background .2s ease,border-color .2s ease,transform .2s ease}
.theme-option:hover{background:rgba(255,255,255,.07);border-color:var(--portal-border);transform:translateY(-1px)}
.theme-option.is-active{background:rgba(255,255,255,.08);border-color:var(--portal-accent-soft)}
.theme-option-copy strong{display:block;font-family:Inter,sans-serif;font-size:.9rem;line-height:1.2rem;color:var(--portal-text)}
.theme-option-copy span{display:block;margin-top:2px;font-family:Inter,sans-serif;font-size:.76rem;line-height:1rem;color:var(--portal-muted-soft)}
.theme-swatch{display:flex;align-items:center;gap:4px}
.theme-swatch i{display:block;width:11px;height:11px;border-radius:9999px;border:1px solid rgba(255,255,255,.08)}
.theme-option[data-theme-value="default"] .theme-swatch i:nth-child(1){background:#0566d9}.theme-option[data-theme-value="default"] .theme-swatch i:nth-child(2){background:#adc6ff}.theme-option[data-theme-value="default"] .theme-swatch i:nth-child(3){background:#d0bcff}
.theme-option[data-theme-value="emerald"] .theme-swatch i:nth-child(1){background:#0f9f6e}.theme-option[data-theme-value="emerald"] .theme-swatch i:nth-child(2){background:#79f2c0}.theme-option[data-theme-value="emerald"] .theme-swatch i:nth-child(3){background:#8bc4ff}
.theme-option[data-theme-value="sunset"] .theme-swatch i:nth-child(1){background:#f97316}.theme-option[data-theme-value="sunset"] .theme-swatch i:nth-child(2){background:#fdba74}.theme-option[data-theme-value="sunset"] .theme-swatch i:nth-child(3){background:#f0abfc}
.ai-chat-typing{display:inline-flex;align-items:center;gap:4px}
.ai-chat-typing span{width:6px;height:6px;border-radius:9999px;background:var(--portal-accent-soft);opacity:.45;animation:aiTyping 1s infinite ease-in-out}
.ai-chat-typing span:nth-child(2){animation-delay:.15s}
.ai-chat-typing span:nth-child(3){animation-delay:.3s}
@keyframes aiTyping{0%,80%,100%{transform:translateY(0);opacity:.35}40%{transform:translateY(-3px);opacity:1}}
@media(max-width:767px){.ai-chat-panel{right:12px;bottom:84px;width:calc(100vw - 24px);height:min(74vh,560px)}.floating-ai-chat{right:14px;bottom:16px}}
@media(max-width:767px){.portal-sidebar{display:none}.portal-main{margin-left:0!important;padding-left:16px!important;padding-right:16px!important}.portal-header{left:0!important;padding-left:16px!important;padding-right:16px!important}.desktop-search{display:none}.theme-menu{right:-6px;min-width:200px}}
</style>
</head>
<body class="font-body-md overflow-x-hidden bg-background text-on-surface" data-user-name="<?= h(user_name()) ?>" data-user-id="<?= h((string) user_id()) ?>">
<aside class="portal-sidebar fixed left-0 top-0 h-screen w-[280px] bg-surface-container dark:bg-surface-container/80 backdrop-blur-xl border-r border-white/5 shadow-2xl shadow-primary-container/20 flex flex-col py-xl px-md space-y-md z-50">
  <div class="flex items-center gap-sm mb-xl">
    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-secondary-container to-tertiary-container flex items-center justify-center">
      <span class="material-symbols-outlined text-white" style="font-variation-settings:'FILL' 1;">school</span>
    </div>
    <div>
      <h1 class="font-headline-md text-headline-md font-extrabold tracking-tight text-primary">CUSIT Portal</h1>
      <p class="font-label-md text-label-md text-on-surface-variant/70"><?= $role === 'admin' ? 'Visionary Admin' : ($role === 'faculty' ? 'Faculty Portal' : 'Student Portal') ?></p>
    </div>
  </div>
  <nav class="flex-1 space-y-base overflow-y-auto scrollbar-hide">
    <?php foreach ($nav as $key => $item): [$label, $icon, $url] = $item; ?>
      <a class="flex items-center gap-md px-md py-sm rounded-lg <?= $active === $key ? 'bg-secondary-container/20 text-secondary-fixed shadow-[inset_0_0_12px_rgba(5,102,217,0.3)] border-r-4 border-secondary-fixed font-bold translate-x-1' : 'text-on-surface-variant/70 font-medium hover:bg-white/10 hover:text-on-surface' ?> transition-all" href="<?= h(base_url($url)) ?>">
        <span class="material-symbols-outlined"><?= h($icon) ?></span>
        <span class="font-label-md text-label-md"><?= h($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="pt-md border-t border-white/5 space-y-base">
    <button class="w-full py-sm bg-gradient-to-r from-secondary-container to-secondary-fixed-dim text-on-secondary font-bold rounded-lg shadow-lg neon-glow-primary mb-md" type="button" data-ai-chat-trigger>Support AI</button>
    <a class="flex items-center gap-md px-md py-sm rounded-lg <?= $active === 'security' ? 'bg-secondary-container/20 text-secondary-fixed shadow-[inset_0_0_12px_rgba(5,102,217,0.3)] border-r-4 border-secondary-fixed font-bold translate-x-1' : 'text-on-surface-variant/70 font-medium hover:bg-white/10 hover:text-on-surface' ?> transition-all" href="<?= h(base_url('security.php')) ?>">
      <span class="material-symbols-outlined">shield</span><span class="font-label-md text-label-md">Security</span>
    </a>
    <a class="flex items-center gap-md px-md py-sm rounded-lg text-error/80 font-medium hover:bg-error/10 hover:text-error transition-all" href="<?= h(base_url('logout.php')) ?>">
      <span class="material-symbols-outlined">logout</span><span class="font-label-md text-label-md">Log Out</span>
    </a>
  </div>
</aside>
<header class="portal-header fixed top-0 right-0 left-[280px] z-40 flex justify-between items-center px-margin-desktop h-20 bg-surface/60 backdrop-blur-md border-b border-white/10 shadow-sm">
  <div class="flex items-center gap-lg">
    <span class="md:hidden font-headline-md text-headline-md font-bold text-primary">CUSIT Portal</span>
    <form class="desktop-search relative group" action="<?= h(base_url('search.php')) ?>" method="get">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/50">search</span>
      <input class="bg-primary-container border border-white/10 rounded-full py-2 pl-10 pr-4 w-80 focus:border-secondary-container focus:outline-none focus:ring-1 focus:ring-secondary-container text-body-sm transition-all" placeholder="Search portal..." type="text" name="q" value="<?= h($searchValue) ?>">
    </form>
  </div>
  <div class="flex items-center gap-md">
    <div class="theme-switcher">
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="Open theme selector" aria-expanded="false">
        <span class="material-symbols-outlined">palette</span>
      </button>
      <div class="theme-menu" id="themeMenu">
        <div>
          <h3 class="theme-menu-title">Portal Themes</h3>
          <p class="theme-menu-subtitle">Switch the mood without changing the layout.</p>
        </div>
        <div class="theme-options">
          <button class="theme-option" type="button" data-theme-value="default">
            <div class="theme-option-copy"><strong>Blue White</strong><span>Blue sidebar with clean white panels</span></div>
            <div class="theme-swatch"><i></i><i></i><i></i></div>
          </button>
          <button class="theme-option" type="button" data-theme-value="emerald">
            <div class="theme-option-copy"><strong>Emerald Night</strong><span>Fresh, calm, modern</span></div>
            <div class="theme-swatch"><i></i><i></i><i></i></div>
          </button>
          <button class="theme-option" type="button" data-theme-value="sunset">
            <div class="theme-option-copy"><strong>Sunset Glow</strong><span>Warm, energetic contrast</span></div>
            <div class="theme-swatch"><i></i><i></i><i></i></div>
          </button>
        </div>
      </div>
    </div>
    <a class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/5 transition-all text-on-surface-variant" href="<?= h(base_url($prefix . '/announcements.php')) ?>"><span class="material-symbols-outlined">notifications</span></a>
    <div class="flex items-center gap-sm ml-sm">
      <img alt="<?= h(user_name()) ?>" class="w-9 h-9 rounded-full border-2 border-primary/20" src="<?= h($avatar) ?>">
      <span class="hidden sm:inline font-label-md text-label-md text-on-surface-variant"><?= h(user_name()) ?></span>
    </div>
  </div>
</header>
<button class="floating-ai-chat" type="button" data-ai-chat-trigger aria-label="Open AI chat" title="Open AI chat">
  <span class="floating-ai-chat-indicator"></span>
  <span class="material-symbols-outlined">smart_toy</span>
</button>
<main class="portal-main ml-[280px] min-h-screen pt-24 px-margin-desktop pb-xl space-y-lg">
<?php
    $flash = flash();
    if ($flash): ?>
    <div class="glass-card rounded-xl p-md border-l-4 <?= $flash['type'] === 'error' ? 'border-error text-error' : 'border-secondary-container text-secondary-fixed' ?>">
      <?= h($flash['message']) ?>
    </div>
<?php endif;
}
