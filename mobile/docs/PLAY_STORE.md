# Play Store listing (not published)

Fill Play Console with this copy. Do not claim the app is live until Google Play shows it as published.

**App name:** Balaji Royal Events  
**Package:** `com.balajiroyalevents.app`  
**Version:** 1.0.0 (versionCode 1)  
**Category:** Events / Lifestyle (choose the closest Play category)  
**Website:** https://www.balajiroyalevents.com/  
**Privacy Policy:** https://www.balajiroyalevents.com/privacy-policy  
**Terms:** https://www.balajiroyalevents.com/terms  
**Support:** https://www.balajiroyalevents.com/contact  
**Contact email:** use the live site support email from `/api/settings` (do not invent one here)

## Short description (max 80)

Balaji Royal Events — wedding and celebration planning in Rajasthan.

## Full description

Balaji Royal Events is the official app for www.balajiroyalevents.com.

Browse wedding and event services, packages, and gallery. Plan your event with a real enquiry to our team. Watch gallery videos in-app. Sign in with your customer account to manage your profile.

Features:

- Home, services, packages, and gallery from the live website
- Plan Your Event enquiry
- Latest updates, FAQs, and reviews
- Call, WhatsApp, email, and directions using published contact details
- Customer login, registration, and email verification

## Assets you must capture from the running app

Do not use mockups.

1. App icon — generated from `assets/brand/logo.png` after Android SDK is available (`dart run flutter_launcher_icons`)
2. Feature graphic — 1024 x 500, brand cream `#FFF8F0` and logo (create in Play Console or a graphics tool)
3. Phone screenshots — Home, Services, Gallery, Enquiry, Account
4. Tablet screenshots — optional 7-inch / 10-inch if you support tablets

## Content rating / audience

- Target audience: 18+ event planners and families
- No user-generated public social feed in this app
- Ads: **No ads** in the current app

## App access

If reviewers need a customer login, provide a **test customer** created for Play review. Do not put production admin passwords in Play Console.

## Data Safety (match actual behavior)

Collected **only when the user uses the feature**:

| Data | Collected? | Shared with | Why |
| --- | --- | --- | --- |
| Name | Yes (register / enquiry / profile) | Balaji Royal Events Laravel API | Account and enquiry |
| Email | Yes (register / login / enquiry if provided) | Same API | Account and enquiry |
| Phone | Yes (register / enquiry) | Same API | Account and enquiry |
| Approximate/precise location | Only if user taps “use current location” | Sent as coordinates to `/geo/reverse` then as **text** in enquiry | Fill event location |
| Contacts | Read on device only when user picks a contact | Selected number may be sent in an enquiry | Fill phone field |
| Photos / video files | Display images from API; authorized download of a gallery original only for verified customers | Download stays on device | Gallery |
| Device IDs / FCM token | **Not collected** (no device-token API) | — | — |
| Payment info | **Not collected** | — | — |

Encryption in transit: HTTPS.  
Account cookies stored in Android/iOS secure storage.  
Users can request account deletion via the website/support process already used by the business.

## Testing tracks

This is a **new** Play app. A new personal developer account typically must complete **closed testing with at least 12 testers for 14 consecutive days** before production. Do not expect same-day public production.

Recommended:

1. Internal testing (up to 100 testers) — first AAB
2. Closed testing — 12+ testers, 14 days
3. Production — only after Play eligibility is met
