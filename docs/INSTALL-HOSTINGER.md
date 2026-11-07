# Installing on Hostinger (or any WordPress.org host)

1. In hPanel create/choose the site and install **WordPress** (Auto Installer). Use the default MySQL/MariaDB database it creates.
2. Log in to wp-admin > **Plugins > Add New > Upload Plugin** > `mnm-foundation-core.zip` > Activate.
3. **Appearance > Themes > Add New > Upload Theme** > `mnm-foundation-theme.zip` > Activate.
4. (Prototype only) **Foundation > Demo Content > Install** to load sample content. For a real launch skip this and create the pages instead: *About Us*, *Marathon*, *Gallery*, *Contact Us*, *Get Involved*, each with the matching **Page Template**, and set a static front page under Settings > Reading. Then build the Primary and Footer menus under Appearance > Menus.
5. **Settings > Permalinks:** choose "Post name" and Save.
6. **Foundation > Website Settings:** confirm contact details and social links, add the real hero photo, then **untick Demonstration mode** and remove demo content.
7. Email: install an SMTP plugin (or use Hostinger's mail) and test the contact and registration emails.
8. Caching: exclude `/marathon/`, `/contact/`, `/get-involved/` from page caching (LiteSpeed Cache: Cache > Excludes).
9. Enable free SSL in hPanel; set Site URL to https.

Database permissions: WordPress needs only the standard privileges on its own database (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP). The plugin creates no custom tables and stores nothing outside WordPress.

Requirements: WordPress 6.2+, PHP 7.4+ (8.x recommended).
