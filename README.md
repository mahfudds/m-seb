# M-SEB — Moodle Secure Exam Browser

[![Moodle Plugin](https://img.shields.io/badge/Moodle-Plugin-orange)](https://moodle.org/plugins/local_mseb)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![CONTRIB-10360](https://img.shields.io/badge/Tracker-CONTRIB--10360-blue)](https://tracker.moodle.org/browse/CONTRIB-10360)

**M-SEB (Moodle Secure Exam Browser)** is a Moodle local plugin that enforces a secure, locked-down quiz environment. It ensures students can only access quizzes through the official **M-SEB ** Android application, while providing configurable JS-based proctoring for iOS and desktop platforms.

---

## ✨ Features

### 🔒 Android Browser Lock
- Forces students to use the official **M-SEB ** Android app for quiz access
- Regular Android browsers are automatically blocked with a clear notification screen
- Whitelists the M-SEB app via user-agent detection

### 💻 Configurable Desktop & iOS Access
- **Allow regular PC browsers** — optionally let students take quizzes from Chrome, Firefox, etc.
- **Pro Guard JS Guard** — applies an escalating penalty timer system if a student switches tabs, minimises the browser, or loses window focus.
- **Safe Exam Browser (SEB) integration** — automatically allows SEB desktop clients.
- **Persistent Violations** — violation counts are stored in `localStorage`, making them persistent even after page refreshes.

### ⏱️ Minimum Time Enforcement
- Set a **minimum working time** (in minutes) before the submit button becomes available.
- Prevents students from rushing through exams without any annoying pop-up alerts.

### 📊 Minimum Answered Percentage
- Require a **minimum percentage of questions** to be answered before submission is allowed.
- Works with both the quiz navigation panel and the summary table.

### 🛡️ Anti-Cheating Measures
- **Tab & App switch detection** — monitors `visibilitychange` events across all platforms.
- **Split-Screen & Multi-Window blocking** — detects and penalizes if the browser window is resized or put into split-screen mode (logically calculated based on screen ratio).
- **Focus Loss monitoring** — detects if the user clicks outside the browser or opens other OS overlays.
- **Anti-translation** — blocks Google Translate overlays during exams.
- **Right-click & clipboard blocking** — prevents copy, cut, and paste operations.
- **Violation counter** — tracks and displays violations with colour-coded severity badges.

---

## 📋 Requirements

| Requirement | Version |
|---|---|
| Moodle | 4.1+ (build 2022112800) |
| PHP | 7.4+ |
| Plugin Type | `local` |

---

## 📦 Installation

### Via ZIP Upload
1. Download the latest release from [GitHub Releases](https://github.com/mahfudds/m-seb/releases) or the [Moodle Plugins Directory](https://moodle.org/plugins/local_mseb)
2. Navigate to **Site Administration → Plugins → Install plugins**
3. Upload the ZIP file and follow the installation prompts
4. Complete the upgrade process

### Via Git
```bash
cd /path/to/moodle/local
git clone https://github.com/mahfudds/m-seb.git mseb
```
Then visit your Moodle site's admin page to complete the installation.

---

## ⚙️ Configuration

M-SEB settings are configured **per quiz** through the standard quiz editing form:

1. Navigate to the quiz activity settings
2. Scroll down to the **M-SEB** section
3. Configure the following options:

| Setting | Description |
|---|---|
| **Enable M-SEB Lock** | Activate browser locking for this quiz |
| **Allow regular PC/Laptop** | Permit access from standard desktop browsers |
| **Enable JS Guard on PC/Laptop** | Apply penalty timer when desktop users switch tabs |
| **Allow iOS Users** | Enable access from iPhone/iPad with JS proctoring |
| **Minimum working time** | Minimum minutes before submit is allowed (0 = disabled) |
| **Minimum answered questions** | Minimum % of questions answered before submit (0 = disabled) |

---

## 🏗️ Architecture

```
local/mseb/
├── amd/src/
│  ├── mintime.js     # ES6 AMD: minimum time & answered enforcement
│  └── proguard.js     # ES6 AMD: Pro Guard proctoring (iOS/PC)
├── backup/moodle2/
│  ├── backup_local_mseb_plugin.class.php
│  └── restore_local_mseb_plugin.class.php
├── classes/privacy/
│  └── provider.php    # Privacy API (null_provider)
├── db/
│  ├── install.xml     # Database schema
│  └── upgrade.php     # Database upgrade steps
├── lang/en/
│  └── local_mseb.php    # English language strings
├── lib.php         # Core callbacks & navigation hooks
└── version.php       # Plugin version metadata
```

### How It Works

1. **Form Integration** — `local_mseb_coursemodule_standard_elements()` injects M-SEB settings into the quiz editing form
2. **Data Persistence** — `local_mseb_coursemodule_edit_post_actions()` saves settings to the `local_mseb` database table
3. **Runtime Enforcement** — `local_mseb_extend_navigation()` intercepts quiz page loads and:
  - Checks user-agent to determine platform (Android, iOS, PC)
  - Blocks unauthorised browsers with a full-page error screen
  - Loads the appropriate AMD JavaScript module for proctoring
4. **Backup/Restore** — Settings are preserved when courses or quiz activities are backed up and restored

---

## 🔐 Privacy

This plugin implements the Moodle Privacy API as a **null provider**. It stores only quiz-level configuration settings (enabled, allowpc, protectpc, allowios, mintime, minanswered) keyed by quiz ID. **No personal user data is collected, stored, or processed.**

---

## 📄 License

This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

Copyright © 2024 M-SEB 

---

## 🤝 Contributing

Contributions are welcome. Please submit issues and pull requests via [GitHub](https://github.com/mahfudds/m-seb).

For plugin approval tracking, see [CONTRIB-10360](https://tracker.moodle.org/browse/CONTRIB-10360).
