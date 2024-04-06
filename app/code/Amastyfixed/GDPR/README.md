# README #
Amastyfixed Gdpr

### What is this module for? ###

* Can keep consent log data on both user login and guest  
* Can migrate existing users to consent log table. Use command **migrate:user-consent-log --consent_id <consent_id>**
* Can migrate existing users who already subscribed/unsubscribed to make consent log. Use command **migrate:newletter-consent-log --consent_id <consent_id>**
* Allow admin unscribe and can opt out guest users from marketing consent from backend
* Log data in newsletter subscribers table
* Store Configuration
  - Manage newsletter subscribtion with checkbox consent
  - Admin Unscribe with decline checkbox consent
### Changelog ###
* version 2.2.0
	- Initialize Module