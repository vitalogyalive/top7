# Top7 - Le Jeu de Pronostic du Top14

![Top7 Logo](/logo.png)

Top7, le jeu de pronostic du Top14 : projet [web](https://www.topseven.fr/).

![GitHub last commit](https://img.shields.io/github/last-commit/pylscblt/top7)
[![Open issue](https://img.shields.io/github/issues/pylscblt/top7)](https://github.com/pylscblt/top7/issues)
[![Closed issue](https://img.shields.io/github/issues-closed/pylscblt/top7)](https://github.com/pylscblt/top7/issues)

---

## 🚀 Quick Start

### For Users
- **Team Agenda:** Manage team events and availability → [Documentation](docs/features/AGENDA.md)
- **Statistics:** View performance graphs → [Documentation](docs/features/STATS_GRAPHS_FEATURE.md)

### For Developers
- **Setup & Testing:** Run automated tests → [Quick Start](docs/testing/README.md)
- **Development Status:** See what's been done → [Changelog](docs/CHANGELOG.md)
- **Modernization Plan:** See the roadmap → [Implementation Status](docs/development/IMPLEMENTATION_STATUS.md)

---

## 📖 Documentation

All documentation is now organized in the [`docs/`](docs/) directory:

### 🎯 Essential Documents

| Document | Description |
|----------|-------------|
| **[Documentation Index](docs/README.md)** ⭐ | Complete guide to all documentation |
| **[Testing Guide](docs/testing/README.md)** | Run automated tests |
| **[Changelog](docs/CHANGELOG.md)** | What's been fixed and added |
| **[Implementation Status](docs/development/IMPLEMENTATION_STATUS.md)** | Current project status |

### 📂 By Category

- **[Features](docs/features/)** - Feature-specific documentation (Agenda, Stats)
- **[Testing](docs/testing/)** - All testing documentation and guides
- **[Development](docs/development/)** - Modernization plan, status, and fixes
- **[Test Scripts](tests/playwright/)** - Automated test suite documentation

---

## 🧪 Testing

### Run the Test Suite

```bash
# Install Playwright (one-time setup)
npx -y playwright install chromium

# Run comprehensive test suite
cd tests/playwright
node test-all-pages.js

# View results
# Linux: xdg-open screenshots/report.html
# Windows: start screenshots/report.html
# Mac: open screenshots/report.html
```

**See [Testing Documentation](docs/testing/README.md) for details.**

---

## ✨ Recent Features

### Team Agenda ✅
Collaborative event management system with availability tracking.
- Create and manage team events
- Track player availability
- Automatic event confirmation
- Mobile-responsive interface

**[Full Documentation](docs/features/AGENDA.md)**

### Statistics Graphs ✅
Interactive performance visualizations using Chart.js.
- Player progression charts
- Team evolution graphs
- Multi-player comparison

**[Full Documentation](docs/features/STATS_GRAPHS_FEATURE.md)**

---

## 🔧 Development

### Project Status

| Area | Status |
|------|--------|
| Security Enhancements | ✅ Complete |
| PHP 8.x Compatibility | ✅ Complete |
| Testing Infrastructure | ✅ Complete |
| Code Refactoring | 🚧 6.5% (15/230 functions) |
| Team Agenda | ✅ Complete |
| Stats Graphs | ✅ Complete |

**See [Implementation Status](docs/development/IMPLEMENTATION_STATUS.md) for details.**

### Tech Stack

- **Backend:** PHP 8.3
- **Database:** MySQL 5.6+
- **Frontend:** Tailwind CSS 4.x, Chart.js 4.4.0
- **Testing:** Playwright (Node.js)
- **Security:** Argon2ID password hashing, CSRF protection

---

## 📊 Testing Stats

- **19+ pages** tested automatically
- **13 test scripts** covering all major features
- **Automated error detection** (PHP warnings, SQL errors, etc.)
- **HTML reports** with screenshots

**See [Test Scripts Documentation](tests/playwright/README.md) for all test files.**

---

## 🐛 Bug Fixes

Recent fixes include:
- ✅ PHP 8.x compatibility (5 issues fixed)
- ✅ Session variable issues
- ✅ SQL GROUP BY errors
- ✅ Array offset warnings
- ✅ Undefined variable warnings

**See [PHP Fixes Summary](docs/development/PHP_WARNINGS_FIXES_SUMMARY.md) for details.**

---

## 📁 Project Structure

```
top7/
├── www/                        # Application code
│   ├── agenda.php             # Team agenda feature
│   ├── stats_graphs.php       # Statistics graphs
│   ├── common.inc             # Shared functions (being refactored)
│   ├── src/                   # Modern PHP classes
│   │   ├── Auth/             # Authentication & passwords
│   │   ├── Database/         # Database layer
│   │   ├── Security/         # CSRF protection
│   │   └── Utils/            # Utilities
│   └── migrations/            # Database migrations
├── tests/
│   └── playwright/            # Automated browser tests
│       ├── README.md         # Test documentation
│       ├── test-all-pages.js # Comprehensive test suite
│       └── ... (13 test files total)
├── docs/                      # All documentation
│   ├── README.md             # Documentation index
│   ├── features/             # Feature docs
│   ├── testing/              # Testing docs
│   └── development/          # Development docs
└── README.md                 # This file
```

---

## 🤝 Contributing

### Getting Started
1. Read the [Documentation Index](docs/README.md)
2. Check [Implementation Status](docs/development/IMPLEMENTATION_STATUS.md)
3. Review [Modernization Plan](docs/development/MODERNIZATION_PLAN.md)
4. Run the test suite to ensure everything works

### Development Workflow
1. Create a feature branch
2. Make your changes
3. Run tests: `cd tests/playwright && node test-all-pages.js`
4. Update documentation
5. Submit a pull request

---

## 📝 License

[License information here]

---

## 🔗 Links

- **Website:** [https://www.topseven.fr/](https://www.topseven.fr/)
- **Documentation:** [docs/README.md](docs/README.md)
- **Testing:** [tests/playwright/README.md](tests/playwright/README.md)
- **Issues:** [GitHub Issues](https://github.com/pylscblt/top7/issues)

---

## 📞 Support

- **Documentation Issues:** Check [docs/README.md](docs/README.md)
- **Testing Issues:** Check [docs/testing/README.md](docs/testing/README.md)
- **Feature Questions:** Check [docs/features/](docs/features/)

---

**Last Updated:** 2025-11-21
**Documentation:** See [docs/](docs/) for complete documentation
