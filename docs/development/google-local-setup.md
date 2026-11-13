# Google Sign-in — Local Setup

This guide prepares the approved real Google sign-in path; it does not create a local password or authentication bypass. Never send the client secret in chat or commit `.env`.

## Provider configuration

1. In the authorised Google Cloud project, configure the Google OAuth consent/app settings according to the organisation's tenant approval requirements. This may require the Workspace administrator; do not assume tenant approval or MFA enforcement from a working callback alone.
2. Create an OAuth client of type **Web application**.
3. Register this exact local redirect URI: `http://127.0.0.1:8000/auth/google/callback`. Use the same host consistently when browsing; `localhost` and `127.0.0.1` are different origins. If you choose another host/port, update the provider registration, APP_URL and GOOGLE_REDIRECT_URI consistently.
4. Enter the client ID/secret and the actual approved Google Workspace hosted domain(s) in local `.env`:

```dotenv
APP_URL=http://127.0.0.1:8000
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
GOOGLE_WORKSPACE_DOMAINS=
```

The domain setting is comma-separated when more than one domain is approved. Leave it unconfigured rather than guessing a MyGovUC domain. The application checks the hosted-domain value obtained directly from Google, independently of any account-chooser hint.

## Application preparation

After a clean pull and the normal verified development-database check, run the pending migrations and `php artisan config:clear`. No seed creates/activates a real user or grants a role. An existing active registered organisational user with an approved explicit assignment must be provisioned from actual verified details before protected access is possible. Do not use the factory test account for this purpose. Initial identity/role administration is an explicit pending setup item, not automatic provisioning.

Start the application with:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/login`. The application-specific public sign-in page is visible before OAuth configuration. Its Google button becomes available after the required configuration is present and configuration cache is cleared. After successful Google authentication, only an eligible active registered identity receives a session.

These first pages use the versioned static `public/abm.css` and `public/abm.js` assets directly. They do not require a running Vite development server. Inter is requested from Google Fonts with a system-font fallback if unavailable.

## Manual acceptance still required

Record actual desktop/mobile account selection, callback success/failure for allowed and unregistered identities, seeded-reference pages, expiry/renewal and logout results in the Batch 008 record. Mocked provider responses and CI screenshot fixtures do not establish real Google configuration, tenant approval, MFA assurance or production readiness.

Provider reference: [Google OpenID Connect / authorization-code and userinfo documentation](https://developers.google.com/identity/openid-connect/openid-connect). This application consumes trusted back-channel userinfo; it does not accept browser-provided ID tokens.
