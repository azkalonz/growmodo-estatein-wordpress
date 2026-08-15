=== Estatein Core ===
Contributors: azkalonz
Tags: real estate, property, inquiries, acf
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Portable structured content and secure inquiry handling for the Estatein theme.

== Description ==

Estatein Core registers property and team content, property taxonomies, private
inquiry records, archive filters, form handlers, lightweight metadata, and
property JSON-LD. ACF Free 6.8.7 can enhance the editor, but is optional: every
frontend accessor falls back to native WordPress post meta and seeded defaults.
Without ACF, property editors still include the native Property Image panel and
Media Library selectors for up to eight additional gallery images.

Valid contact, property-inquiry, and newsletter submissions are stored before
email is attempted, so a hosting mail failure cannot lose a request.

== Installation ==

1. Upload and activate Estatein Core.
2. Optionally install ACF Free 6.8.7 for the structured editor controls.
3. Activate the Estatein theme.
4. Run `wp estatein seed` once to build the idempotent demonstration dataset.

== Form contract ==

Post forms to `admin-post.php` with one of these actions:

* `estatein_submit_contact` with nonce field `estatein_contact_nonce` for action `estatein_submit_contact`.
* `estatein_submit_inquiry` with nonce field `estatein_inquiry_nonce` for action `estatein_submit_inquiry`.
* `estatein_subscribe` with nonce field `estatein_subscribe_nonce` for action `estatein_subscribe`.

All forms include an empty `website` honeypot. Contact and inquiry forms require
first name, last name, valid email, message, and a checked `terms` field. Property
inquiries additionally require a valid `property_id`.

== Uninstallation ==

Deactivation and ordinary uninstallation preserve all content. To permanently
remove plugin-owned posts and taxonomy terms, define `ESTATEIN_CORE_DELETE_DATA`
as true before uninstalling. This operation cannot be undone.
