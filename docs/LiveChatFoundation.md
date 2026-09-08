# Live Chat Foundation Notes

Future Client Dashboard live chat resources should use:

```php
auth()->user()?->hasLiveChatAccess()
```

Apply this centralized condition before registering or displaying Live Chat
navigation for:

- Live Inbox
- Waiting Chats
- Active Chats
- Agents
- Canned Replies
- Live Chat Settings

The helper allows Super Admin users and checks the authenticated owner or
agent user's own `company_id`. Company-level access is true when at least one
website in that company has `website_settings.enable_live_chat = true`.
