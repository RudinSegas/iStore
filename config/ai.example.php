<?php
// The real key belongs in ignored config/ai.php or an environment variable.
return [
    'enabled' => getenv('AI_ENABLED') === '1',
    'api_key' => getenv('GEMINI_API_KEY') ?: '',
    'model' => getenv('GEMINI_MODEL') ?: 'gemini-3.5-flash-lite',
    'rate_secret' => getenv('AI_RATE_SECRET') ?: '', // random 32+ characters, private
    'timeout_seconds' => (int)(getenv('AI_TIMEOUT_SECONDS') ?: 25),
    'session_per_minute' => 5,
    'ip_per_minute' => 10,
    'global_calls_per_minute' => 10,
    'global_calls_per_day' => 100,
    'connectivity_test_enabled' => false,
    'connectivity_test_token' => '', // temporary random 32+ characters
];
