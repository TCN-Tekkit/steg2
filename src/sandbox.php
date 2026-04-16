<?php
use PHPSandbox\PHPSandbox;

function SB_String(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// opretter en gjenbrukbar sandbox-instans
function create_sandbox(): PHPSandbox {
    $sb = new PHPSandbox();

    // whitelist sikre funksjoner som sandbox kode trenger
    $sb->whitelist_func(['sandBox', 'htmlspecialchars', 'escapeString', 'SB_String']);
    return $sb;
}
?>