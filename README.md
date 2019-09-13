# quickgallery
[![Deploys with Katapult](https://img.shields.io/badge/deploys_with-katapult-orange.svg?style=flat-square)](https://github.com/jmversteeg/katapult)

### Usage

1. Insert thumbnails using the built-in editor
2. Surround said thumbnails with `[quickgallery]` and `[/quickgallery]`

### Installation

```json
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/jmversteeg/quickgallery.git"
    }
  ],
  "require": {
    "jmversteeg/quickgallery": "dev-master"
  },
```

Then ship your build with [katapult](https://github.com/jmversteeg/katapult), or:

```bash
cd web/app/mu-plugins/quickgallery
npm install
grunt build
```
