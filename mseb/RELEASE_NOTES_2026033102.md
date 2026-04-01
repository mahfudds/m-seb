# ![alt text](image.png)
**Build Version:** `2026033102` | **Release Date:** 2026-03-31

Greetings! This release focuses on stability and security enhancements to ensure a smoother experience for both administrators and students.

## 🚀 Key Improvements

### 🛠️ Protocol Stability (HTTP/2 Support)
- **Problem**: Some users experienced `ERR_HTTP2_PROTOCOL_ERROR` on the access-blocked page when using modern browsers or HTTP/2 enabled servers.
- **Solution**: Modernized the header handling by replacing manual PHP header strings with `http_response_code(403)`. This ensures consistent behavior across all HTTP protocols and server environments (Nginx/Apache).

### 🛡️ Enhanced Access Control
- **Desktop Mode Detection**: Students frequently attempted to bypass the Android app restriction by switching their mobile browser to "Desktop Site" mode. We have implemented a new detection mechanism that identifies and blocks these attempts effectively.
- **Independent Pro Guard (JS Guard)**: You can now keep the **JS Guard (Penalty Timer)** active for PC/iOS students even if the strict **Android Lock** (which requires the M-SEB App) is disabled. This provides flexible proctoring options for mixed-device exams.

### 🎨 UI/UX Refinement
- **Blocked Page Improvements**: The blocking screen has been redesigned with a sleek Dark Mode aesthetic, providing clearer instructions to students on how to proceed.
- **Improved Performance**: Reduced redundant JavaScript execution in the background for a more lightweight student experience.

## ⚙️ Server-Side Recommendations (Nginx)
If your Moodle instance has many plugins or large cookies, you might encounter header size issues. We recommend adding the following lines to your Nginx configuration within the `http { ... }` or `server { ... }` block:

```nginx
# Handle large Moodle cookies & headers
large_client_header_buffers 4 32k;
fastcgi_buffer_size 32k;
fastcgi_buffers 8 16k;
```

## 📥 Installation/Upgrade
1.  Download the latest package: `local_mseb_2026033102.zip`.
2.  Extract and replace the `/local/mseb/` directory on your Moodle server.
3.  As a Moodle Admin, navigate to **Site Administration > Notifications** to run the database upgrade.
4.  Verify the version in the plugin settings (Should show `2026033102`).

---
*© 2024-2026 M-SEB Team. Supporting Integrity in Digital Examinations.*
