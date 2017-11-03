<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 11/2/2017 AD
 * Time: 19:59
 */

namespace MiladRahimi\LaraJwt\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class JwtAuth implements JwtAuthInterface
{
    /**
     * @inheritdoc
     */
    public function generateTokenFrom($user, array $claims = []): string
    {
        if ($user instanceof Authenticatable) {
            $user = $user->getAuthIdentifier();
        }

        $tokenClaims = [];
        $tokenClaims['sub'] = $user;
        $tokenClaims['iss'] = app('config')->get('jwt.issuer');
        $tokenClaims['aud'] = app('config')->get('jwt.audience');
        $tokenClaims['exp'] = time() + intval(app('config')->get('jwt.ttl'));
        $tokenClaims['iat'] = time();
        $tokenClaims['nbf'] = time();
        $tokenClaims['jti'] = uniqid('jwt');

        foreach ($claims as $name => $value) {
            $tokenClaims[$name] = $value;
        }

        $jwtService = app(JwtServiceInterface::class);
        return $jwtService->generate($tokenClaims, app('config')->get('jwt.key'));
    }

    /**
     * @inheritdoc
     */
    public function retrieveClaimsFrom(string $jwt): array
    {
        /** @var JwtServiceInterface $jwtService */
        $jwtService = app(JwtServiceInterface::class);

        return $jwtService->parse($jwt, app('config')->get('jwt.key'));
    }

    /**
     * @inheritdoc
     */
    public function retrieveUserFrom(string $jwt, $provider = null): Authenticatable
    {
        $claims = $this->retrieveClaimsFrom($jwt);

        /** @var UserProvider $provider */
        $provider = app('auth')->getProvider($provider);

        return $provider->retrieveById(($claims['sub'] ?? null));
    }
}