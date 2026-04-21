<?php

/**
 * UserToken entity representing the user_tokens table
 * Used for remember me functionality
 */
class UserToken
{
    private $id;
    private $user_id;
    private $selector;
    private $token_hash;
    private $expires_at;

    public function __construct($user_id, $selector, $token_hash, $expires_at, $id = null)
    {
        $this->user_id = $user_id;
        $this->selector = $selector;
        $this->token_hash = $token_hash;
        $this->expires_at = $expires_at;
        $this->id = $id;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function getTokenHash()
    {
        return $this->token_hash;
    }

    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    public function setTokenHash($token_hash)
    {
        $this->token_hash = $token_hash;
    }

    public function setExpiresAt($expires_at)
    {
        $this->expires_at = $expires_at;
    }

    /**
     * Check if the token has expired
     */
    public function isExpired()
    {
        return strtotime($this->expires_at) < time();
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'selector' => $this->selector,
            'token_hash' => $this->token_hash,
            'expires_at' => $this->expires_at
        ];
    }
}
?>
