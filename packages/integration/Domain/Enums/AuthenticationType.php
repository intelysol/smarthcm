<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Domain\Enums;

enum AuthenticationType: string
{
    case None = 'none';
    case ApiKey = 'api_key';
    case BearerToken = 'bearer_token';
    case BasicAuth = 'basic_auth';
    case OAuth2AuthorizationCode = 'oauth2_auth_code';
    case OAuth2ClientCredentials = 'oauth2_client_credentials';
    case HmacSignature = 'hmac_signature';
    case MutualTls = 'mutual_tls';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None / Public',
            self::ApiKey => 'API Key (Header / Query)',
            self::BearerToken => 'Bearer Token',
            self::BasicAuth => 'Basic Authentication',
            self::OAuth2AuthorizationCode => 'OAuth 2.0 (Authorization Code)',
            self::OAuth2ClientCredentials => 'OAuth 2.0 (Client Credentials)',
            self::HmacSignature => 'HMAC Signature',
            self::MutualTls => 'Mutual TLS (mTLS)',
        };
    }
}
