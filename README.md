<p align="center">
    <a href="https://youtu.be/aOA1m9dFEww" target="_blank">
        <img src="/art/video.jpg" alt="Overview PAO" style="width:70%;">
    </a>
</p>


<p align="center">
    <img src="https://raw.githubusercontent.com/nunomaduro/pao/main/art/logo.png" alt="PAO" width="300">
    <p align="center">
        <a href="https://github.com/nunomaduro/pao/actions"><img alt="GitHub Workflow Status (master)" src="https://github.com/nunomaduro/pao/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/nunomaduro/pao"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/nunomaduro/pao"></a>
        <a href="https://packagist.org/packages/nunomaduro/pao"><img alt="Latest Version" src="https://img.shields.io/packagist/v/nunomaduro/pao"></a>
        <a href="https://packagist.org/packages/nunomaduro/pao"><img alt="License" src="https://img.shields.io/packagist/l/nunomaduro/pao"></a>
    </p>
</p>

------

**PAO** is agent-optimized output for PHP tools. It works with any PHP project — **Laravel**, **Symfony**, **Laminas**, **vanilla PHP**, or anything else that uses **PHPUnit**, **Pest**, **Paratest**, **PHPStan**, or **Laravel Artisan**.

It detects when your tools are running inside an AI agent — **Claude Code**, **Cursor**, **Devin**, **Gemini CLI**, and others — and replaces the verbose, human-readable output with compact, super minimal, structured JSON (or optionally YAML via `PAO_FORMAT=yaml`). For Laravel Artisan commands, it strips ANSI colors, box-drawing characters, and excess whitespace. Zero config — just install and it works.

## 🔥 Benchmarks

PAO output is **constant at ~20 tokens** — no matter how large your test suite is.

### 🚀 1,000 Tests

| Runner | Without PAO | With PAO ⚡️ | Tokens Saved | Reduction |
|---|---|---|---|---|
| PHPUnit | 336 tokens | **20 tokens** | 316 | 🟢 **94%** |
| Paratest | 351 tokens | **20 tokens** | 331 | 🟢 **94%** |
| Pest | 10,123 tokens | **20 tokens** | 10,103 | 🔥 **99.8%** |
| Pest --parallel | 11,125 tokens | **20 tokens** | 11,105 | 🔥 **99.8%** |

> 💡 **The bigger your test suite, the more you save.** Pest goes from **11,125 → 20 tokens** at 1,000 tests. That's **99.8% fewer tokens** your AI agent needs to process!

### 🔍 PHPStan

| Scenario | Without PAO | With PAO ⚡️ | Reduction |
|---|---|---|---|
| Clean (0 errors) | ~50 tokens | **5 tokens** | 🟢 **90%** |
| 10 errors | ~250 tokens | **~100 tokens** | 🟢 **60%** |

### 🛠️ Laravel Artisan

| Command | Without PAO | With PAO ⚡️ | Reduction |
|---|---|---|---|
| `about` | 528 tokens | **134 tokens** | 🔥 **74%** |
| `db:show` | 390 tokens | **102 tokens** | 🔥 **73%** |
| `migrate:status` | 82 tokens | **46 tokens** | 🟢 **44%** |

### 💰 Cost Savings Per Session

A single run saves ~10K tokens with Pest. But in a real coding session you might run your test suite **20-50+ times** and Artisan commands **10-20+ times**. With **1,000 Pest tests** and **50 runs**, that's **~500K tokens saved**:

| Model | 50 test runs without PAO | 50 test runs with PAO ⚡️ | Saved per session |
|---|---|---|---|
| Sonnet 4 | $1.52 | $0.003 | 🟢 **$1.52** |
| Opus 4 | $7.58 | $0.015 | 🔥 **$7.56** |

Add in 20 Artisan commands (`about`, `db:show`, `migrate:status`, etc.) and PHPStan runs per session, and the savings compound further — both in cost and context window space.

<details>
<summary>How this was calculated</summary>

- **Test token counts** measured by running `vendor/bin/pest` with 1,002 tests (100 test files, 10 tests each + 2 feature tests) in a Laravel app, counting output characters and estimating ~4 characters per token
- **Artisan token counts** measured by running `php artisan about`, `db:show`, and `migrate:status` in a Laravel 13 app with default configuration
- **Cost per token**: based on published input pricing as of March 2026 — Sonnet 4 at $3/MTok, Opus 4 at $15/MTok
- **Assumes**: tool output counts as input tokens (agent reads the output). Does not account for output tokens, caching, or batch discounts
</details>

But the real win isn't cost — it's **context window space**. Every test run without PAO dumps 10K+ tokens of dots, checkmarks, and stack traces into your agent's context. Every Artisan command adds hundreds of tokens of dot separators and box-drawing characters. After a full coding session, that's **hundreds of thousands of tokens of decorative output competing with your actual code, conversation, and reasoning** for the same limited context window. PAO keeps all of that minimal — freeing your agent to focus on what matters.

## ⚡️ Installation

> **Requires [PHP 8.3+](https://php.net/releases/)** — Works with **PHPUnit 12-13**, **Pest 4-5**, **Paratest**, **PHPStan**, and **Laravel 12+**.

```bash
composer require nunomaduro/pao:^0.1 --dev
```

That's it. PAO hooks into PHPUnit, Pest, Paratest, and PHPStan automatically through Composer's autoloader. For Laravel projects, a service provider is auto-discovered to clean Artisan command output.

> **🛡️ PAO only activates when it detects an AI agent** (Claude Code, Cursor, Devin, Gemini CLI, etc.). When you or your team run tools directly in the terminal, the output is completely unchanged — same colors, same formatting, same experience. Zero impact on human workflows.

## ⚙️ Configuration

PAO is configured through environment variables:

| Variable | Values | Default | Description |
|---|---|---|---|
| `PAO_DISABLE` | any value | *(unset)* | Disable PAO entirely |
| `PAO_FORMAT` | `json`, `pretty`, `yaml` | `json` | Output format |

### Pretty JSON

Set `PAO_FORMAT=pretty` for human-readable JSON:

```json
{
    "result": "passed",
    "tests": 1002,
    "passed": 1002,
    "duration_ms": 321
}
```

### YAML

Set `PAO_FORMAT=yaml` for YAML output:

```yaml
result: passed
tests: 1002
passed: 1002
duration_ms: 321
```

Both options are useful when reviewing agent output directly in the terminal. JSON remains the default.

## ✨ Before & After

Your test suite with **1,000 tests** goes from this:

```
PHPUnit 12.5.14 by Sebastian Bergmann and contributors.

.............................................................   61 / 1002 (  6%)
.............................................................  122 / 1002 ( 12%)
...
..........................                                    1002 / 1002 (100%)

Time: 00:00.321, Memory: 46.50 MB

OK (1002 tests, 1002 assertions)
```

To this:

```json
{
  "result": "passed",
  "tests": 1002,
  "passed": 1002,
  "duration_ms": 321
}
```

🤯 That's up to **99.8% fewer AI tokens**. The output is **constant-size** regardless of how many tests you have — and when tests fail, it includes file paths, line numbers, and failure messages.

Extra output from Pest plugins like `--coverage` or `--profile` is captured, cleaned of ANSI codes and decorations, and included as a `raw` array in the JSON:

```json
{
  "result": "passed",
  "tests": 1002,
  "passed": 1002,
  "duration_ms": 1520,
  "raw": [
    "Http/Controllers/Controller 100.0%",
    "Models/User 0.0%",
    "Total: 33.3 %"
  ]
}
```

### Laravel Artisan

When installed in a Laravel 12+ application, PAO automatically cleans Artisan command output in agent environments — stripping ANSI colors, box-drawing characters, dot separators, and excess whitespace:

```
# Before (without PAO) — 2,111 characters
  Environment ................................................................
  Application Name ................................................... Laravel
  Laravel Version ..................................................... 13.3.0
  PHP Version .......................................................... 8.5.4
  Debug Mode ......................................................... ENABLED

# After (with PAO) — 535 characters
 Environment ..
 Application Name .. Laravel
 Laravel Version .. 13.3.0
 PHP Version .. 8.5.4
 Debug Mode .. ENABLED
```

Up to **75% fewer tokens** on commands like `about`, `db:show`, and `migrate:status` — same information, no decoration.

### PHPStan

PHPStan output is also converted to structured JSON:

```json
{
  "result": "failed",
  "errors": 2,
  "error_details": [
    {
      "file": "/app/Http/Controllers/Controller.php",
      "line": 9,
      "message": "Method Controller::index() should return int but returns string.",
      "identifier": "return.type"
    },
    {
      "file": "/app/Http/Controllers/Controller.php",
      "line": 14,
      "message": "Call to an undefined method Controller::doesNotExist().",
      "identifier": "method.notFound"
    }
  ]
}
```

When all checks pass: `{"result":"passed","errors":0}`

---

**PAO** was created by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
