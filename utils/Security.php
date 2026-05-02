<?php
class Security
{
    private static $cipher;
    private static $key;
    private static $loaded = false;

    private static function loadConfig()
    {
        if (self::$loaded) return;

        self::$cipher = $_ENV['ENCRYPTION_CIPHER'] ?? 'aes-256-cbc';
        self::$key = $_ENV['ENCRYPTION_KEY'] ?? '';

        if (empty(self::$key)) {
            throw new \RuntimeException('ENCRYPTION_KEY must be set in environment');
        }

        self::$loaded = true;
    }
    /**
     * Hash a password using bcrypt
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    /**
     * Encrypts an array of data into a secure string
     * @param array $data ['id' => 1, 'role' => 'admin', 'email' => '...']
     * @return string
     */
    public static function encryptToken(array $data)
    {
        self::loadConfig();
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = random_bytes($ivLength); // Generate a random IV

        $jsonData = json_encode($data);

        // Encrypt the data
        $encryptedRaw = openssl_encrypt($jsonData, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);

        // Combine IV + Encrypted Data and encode to Base64 for cookie storage
        // We use HMAC to ensure the token hasn't been tampered with
        $hmac = hash_hmac('sha256', $encryptedRaw . $iv, self::$key, true);

        return base64_encode($iv . $hmac . $encryptedRaw);
    }

    /**
     * Decrypts the token and returns the original array
     * @param string $token
     * @return array|null Returns null if tampering is detected
     */
    public static function decryptToken($token)
    {
        self::loadConfig();
        $c = base64_decode($token);
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $hmacLength = 32; // SHA256 hmac length

        // Extract the pieces
        $iv = substr($c, 0, $ivLength);
        $hmac = substr($c, $ivLength, $hmacLength);
        $encryptedRaw = substr($c, $ivLength + $hmacLength);

        // Verify the HMAC (Integrity Check)
        $calculatedHmac = hash_hmac('sha256', $encryptedRaw . $iv, self::$key, true);

        if (!hash_equals($hmac, $calculatedHmac)) {
            return null; // Token was modified!
        }

        $decrypted = openssl_decrypt($encryptedRaw, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
        $data = json_decode($decrypted, true);

        // Check if token is expired based on the 'exp' key we set
        if (isset($data['exp']) && time() > $data['exp']) {
            return null; // Token expired
        }

        return $data;
    }

    /**
     * Generates an encrypted token and sets it as a secure cookie
     * @param array $user Array containing id, role, and email
     * @param int $days How many days the cookie should last
     */
    public static function setRememberMeCookie(array $user, $days = 30)
    {
        $expiryTime = time() + ($days * 24 * 60 * 60);

        // Prepare the payload with an internal expiration check
        $payload = [
            'id' => $user['id'],
            'role' => $user['role'],
            'email' => $user['email'],
            'exp' => $expiryTime // Stored inside for server-side validation
        ];

        // Encrypt using our Security class
        $encryptedToken = Security::encryptToken($payload);

        // Set the cookie with security flags
        // PHP 7.3+ supports the array-based options for better readability
        setcookie('remember_me', $encryptedToken, [
            'expires' => $expiryTime,
            'path' => '/',
            'domain' => '', // Set your domain if needed
            'secure' => false,     // Sent only over HTTPS
            'httponly' => true,     // Not accessible via JavaScript
            'samesite' => 'Lax'     // Protection against CSRF
        ]);
    }

    /**
     * Clear the cookie (used for logout)
     */
    public static function clearRememberMeCookie()
    {
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['remember_me']);
    }
}
