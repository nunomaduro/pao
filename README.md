<p align="center">
    <a href="https://youtu.be/aOA1m9dFEww" target="_blank">
        <img src="/art/video.jpg" alt="Overview PAO" style="width:70%;">
    </a>
</p>

> 🚧 **Work in progress** — PAO is under active development. Want to try it early? Install from dev:
> ```bash
> composer require nunomaduro/pao:0.x-dev --dev
> ```
> Then just run your tests with any AI agent — the output will be JSON automatically. Feedback and bug reports are welcome at [github.com/nunomaduro/pao/issues](https://github.com/nunomaduro/pao/issues).

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

**PAO** is agent-optimized output for PHP testing tools. It works with any PHP project — **Laravel**, **Symfony**, **Laminas**, **vanilla PHP**, or anything else that uses **PHPUnit**, **Pest**, or **Paratest**.

It detects when your tests are running inside an AI agent — **Claude Code**, **Cursor**, **Devin**, **Gemini CLI**, and others — and replaces the verbose, human-readable output with compact, super minimal, structured JSON. Zero config — just install and it works.

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

### 💰 Cost Savings Per Session

A single run saves ~10K tokens with Pest. But in a real coding session you might run your test suite **20-50+ times**. With **1,000 Pest tests** and **50 runs**, that's **~500K tokens saved**:

| Model | 50 runs without PAO | 50 runs with PAO ⚡️ | Saved per session |
|---|---|---|---|
| Sonnet 4 | $1.52 | $0.003 | 🟢 **$1.52** |
| Opus 4 | $7.58 | $0.015 | 🔥 **$7.56** |

<details>
<summary>How this was calculated</summary>

- **Token counts** measured by running `vendor/bin/pest` with 1,002 tests (100 test files, 10 tests each + 2 feature tests) in a Laravel app, counting output characters and estimating ~4 characters per token
- **Cost per token**: based on published input pricing as of March 2026 — Sonnet 4 at $3/MTok, Opus 4 at $15/MTok
- **Assumes**: test output counts as input tokens (agent reads the output). Does not account for output tokens, caching, or batch discounts
</details>

But the real win isn't cost — it's **context window space**. Every test run without PAO dumps 10K+ tokens of dots, checkmarks, and stack traces into your agent's context. After 50 runs, that's **500K tokens of test output competing with your actual code, conversation, and reasoning** for the same limited context window. PAO keeps that to ~1K tokens total — freeing your agent to focus on what matters.

## ⚡️ Installation

> **Requires [PHP 8.3+](https://php.net/releases/)** — Works with **PHPUnit 12-13**, **Pest 4-5**, and **Paratest**.

```bash
composer require nunomaduro/pao:0.x-dev --dev
```

That's it. PAO hooks into PHPUnit, Pest, and Paratest automatically through Composer's autoloader.

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
  "duration_ms": 321,
  "memory_mb": 46.5
}
```

🤯 That's up to **99.8% fewer AI tokens**. The output is **constant-size** regardless of how many tests you have — and when tests fail, it includes file paths, line numbers, and failure messages.

When tests are slow, you can identify them by passing a custom threshold (default is `500`ms):

```bash
vendor/bin/pest --slow-tests-threshold=100
```

Which adds a `slow_tests` array to the JSON:

```json
{
  "result": "passed",
  "tests": 1002,
  "passed": 1002,
  "duration_ms": 1520,
  "memory_mb": 46.5,
  "slow_tests": [
    { "name": "UserTest::it_imports_bulk", "duration_ms": 1240 }
  ]
}
```

Extra output from Pest plugins like `--coverage` or `--profile` is captured, cleaned of ANSI codes and decorations, and included as an `output` array in the JSON:

```json
{
  "result": "passed",
  "tests": 1002,
  "passed": 1002,
  "duration_ms": 1520,
  "memory_mb": 46.5,
  "output": [
    "Http/Controllers/Controller 100.0%",
    "Models/User 0.0%",
    "Total: 33.3 %"
  ]
}
```

---

**PAO** was created by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
