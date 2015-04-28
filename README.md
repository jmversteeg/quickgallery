# quickgallery
*The smart, responsive WordPress gallery that just works*

### Sounds amazing! How the hell does it work?

1. Insert thumbnails using the built-in editor
2. Surround said thumbnails with `[quickgallery]` and `[/quickgallery]`
3. That's actually it
4. Holy shit that's so cool
5. I know

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

Then ship your build with (katapult)[https://github.com/jmversteeg/katapult], or:

```bash
cd web/app/mu-plugins/quickgallery
npm install
grunt build
```