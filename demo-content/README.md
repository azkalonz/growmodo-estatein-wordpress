# Demo-content export

The canonical fixture is the idempotent companion-plugin command:

```bash
make seed
```

After seeding, produce the equivalent WordPress eXtended RSS export for a host without WP-CLI:

```bash
make export
```

This writes `estatein-demo-content.xml` into this directory. The export step replaces localhost attachment URLs with the corresponding committed assets in the public `main` branch, because WXR stores source URLs rather than binary files. Set `ESTATEIN_WXR_ASSET_BASE` before export if the repository owner/branch changes.

Import through **Tools → Import → WordPress** after activating the Estatein Core plugin and Estatein theme. Essential visual fallbacks are bundled with the theme if a host blocks remote attachment downloads. Re-save **Settings → Permalinks** and assign the Primary/Footer menu locations after an import.
