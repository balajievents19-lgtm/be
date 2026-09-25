# Deep links

Package: `com.balajiroyalevents.app`  
Host: `www.balajiroyalevents.com`

App routes already mapped:

- `/services/{slug}`
- `/gallery/{slug}`
- `/blog/{slug}` → in-app `/updates/{slug}`
- `/packages`
- `/contact` → `/enquiry`
- `/account`

## Missing values (do not invent)

- Android upload-key **SHA-256** (from `keytool -list -v`)
- Apple **Team ID** (Apple Developer membership)

Templates:

- `store/well-known/assetlinks.json`
- `store/well-known/apple-app-site-association`

## Deploy on the live site (owner action)

Serve both files over HTTPS **without** modifying other vhosts.

Suggested public URLs:

- `https://www.balajiroyalevents.com/.well-known/assetlinks.json`
- `https://www.balajiroyalevents.com/.well-known/apple-app-site-association`

`apple-app-site-association` should be served as `application/json` with **no** `.json` filename if possible.

Do not change Nginx for other brands. Only add these two static files on the Balaji Royal Events site when SHA-256 and Team ID are known.

## SHA-256 after a Play App Signing enrollment

If Play App Signing is enabled, use the **App signing key certificate** SHA-256 from Play Console → Setup → App integrity, not only the upload key.
