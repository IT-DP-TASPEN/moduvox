<?php
// Forward request to the built React app (Vite)
// This file works alongside .htaccess for local subdomain deployment
require_once __DIR__ . '/dist/index.html';
