# Visual reference matrix

Run `npm run screenshots` against the seeded local site. The command scrolls through each page to load native lazy media, returns to the top, and writes full-page captures to `reports/screenshots/{390,1440,1920}/` for manual overlay comparison.

| Page             | 1920 desktop | 1440 laptop | 390 mobile  |
| ---------------- | ------------ | ----------- | ----------- |
| Home             | `46:304`     | `139:6238`  | `139:7812`  |
| About Us         | `89:5151`    | `143:9031`  | `146:10636` |
| Properties       | `97:7288`    | `149:12282` | `150:13561` |
| Property Details | `102:8754`   | `165:2`     | `170:1233`  |
| Services         | `104:10350`  | `170:2308`  | `172:3548`  |
| Contact          | `104:12305`  | `172:5138`  | `172:6494`  |

Compare at 100% scale. Check section order, container edges, type wrapping, image crop, card dimensions, border radii, and vertical rhythm before inspecting decorative details. The responsive suite separately checks 320, 768, and 1024 pixels for overflow.
