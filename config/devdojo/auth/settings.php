<?php

/*
 * These are some default authentication settings
 */
return [
    'redirect_after_auth' => '/',
    'registration_enabled' => true,
    'registration_show_password_same_screen' => true,
    'registration_include_name_field' => false,
    'registration_include_phone_field' => false,
    'registration_include_password_confirmation_field' => false,
    'registration_require_email_verification' => false,
    'enable_branding' => true,
    'dev_mode' => false,
    'enable_2fa' => false, // Enable or disable 2FA functionality globally
    'enable_email_registration' => true,
    'login_show_social_providers' => true,
    'center_align_social_provider_button_content' => false,
    'center_align_text' => false,
    'social_providers_location' => 'bottom',
    'check_account_exists_before_login' => true,

    'login_show_subheadline' => true,
    'register_show_subheadline' => true,
    'passwordResetRequest_show_subheadline' => true,
    'passwordReset_show_subheadline' => true,
    'passwordConfirm_show_subheadline' => true,
    'verify_show_subheadline' => true,
    'twoFactorChallenge_show_subheadline_auth' => true,
    'twoFactorChallenge_show_subheadline_recovery' => true,
    'invite_show_subheadline' => true,
    'expired_show_subheadline' => true,

];
