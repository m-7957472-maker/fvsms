<?php
// clear opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'opcache_reset: ok';
} else {
    echo 'opcache not available';
}
